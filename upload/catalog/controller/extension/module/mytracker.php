<?php

/**
 * @package mytracker
 */

use GuzzleHttp\Client;

class ControllerExtensionModuleMyTracker extends Controller {
	/**
	 * Базовый URL для API.
	 */
	const API_BASE = 'https://tracker-s2s.my.com/v1/';

	/**
	 * Экземпляр класса GuzzleHttp.
	 *
	 * @var Client
	 */
	private Client $client;

	/**
	 * Идентификатор приложения.
	 *
	 * @var int $app_id
	 */
	private int $app_id;

	/**
	 * Ключ (токен) приложения.
	 *
	 * @var string $api_key
	 */
	private string $api_key;

	/**
	 * Конструктор класаа.
	 *
	 * @param $registry
	 */
	public function __construct($registry)
	{
		parent::__construct($registry);

		$this->app_id  = (int) $this->config->get('module_mytracker_app_id');
		$this->api_key = $this->config->get('module_mytracker_api_key') ?? '';

		$this->client = new Client([
			'base_url' => self::API_BASE,
		]);
	}

	/**
	 * Получает идентификатор приложения.
	 *
	 * @return int
	 */
	public function getAppId(): int
	{
		return $this->app_id;
	}

	/**
	 * Получает токен приложения.
	 *
	 * @return string
	 */
	public function getApiKey(): string
	{
		return $this->api_key;
	}

	/**
	 * Выводит код счётчика во фронтенде.
	 *
	 * @param   string  $route  Маршрут.
	 * @param   array   $data   Массив данных.
	 * @param   string  $output Вывод.
	 *
	 * @return void
	 */
	public function code(string &$route, array &$data, &$output)
	{
		if (!$this->config->get('module_mytracker_status')) {
			return;
		}

		if (!$this->config->get('module_mytracker_counter_id')) {
			return;
		}

		$data['module_mytracker_user_id'] = $this->customer->getId();
		$data['module_mytracker_counter_id'] = $this->config->get('module_mytracker_counter_id');
		$data['module_mytracker_tracking_user'] = $this->config->get('module_mytracker_tracking_user') && $this->customer->isLogged();
		$data['module_mytracker_domain'] = (int) $this->config->get('module_mytracker_domain') === 1
			? 'top-fwz1.mail.ru'
			: 'mytopf.com';

		$module = $this->load->view('extension/module/mytracker/code', $data);
		$output = str_replace('<body>',  '<body>' . $module,  $output);
	}

	/**
	 * Обрабатывает событие входа пользователя.
	 *
	 * @param   string  $route  Маршрут.
	 * @param   array   $data   Массив данных.
	 * @param   string  $output Вывод.
	 *
	 * @return void
	 */
	public function onCustomerLogin($route, $data, $output)
	{
		if (!$this->config->get('module_mytracker_tracking_login')) {
			return;
		}

		if ($this->customer->isLogged()) {
			$customerId = $this->customer->getId();
		} elseif (!empty($data[0])) {
			$customerId = $this->model_account_customer->getCustomerByEmail($data[0])['customer_id'];
		} else {
			$customerId = 0;
		}

		$this->sendLoginEvent(
			[
				'customUserId' => $customerId,
			]
		);
	}

	/**
	 * Обрабатывает событие авторизации пользователя.
	 *
	 * @param   string  $route  Маршрут.
	 * @param   array   $data   Массив данных.
	 * @param   string  $output Вывод.
	 *
	 * @return void
	 */
	public function onCustomerRegistration($route, $data, $output)
	{
		if (!$this->config->get('module_mytracker_tracking_registration')) {
			return;
		}

		$this->sendRegistrationEvent(
			[
				'customUserId' => $output,
			]
		);
	}

	/**
	 * Отправка события о регистрации.
	 *
	 * @param array $data Данные по пользователю.
	 *
	 * @return bool
	 */
	public function sendRegistrationEvent( array $data ): bool {
		return $this->request( 'registration', $data );
	}

	/**
	 * Отправка события об авторизации.
	 *
	 * @param array $data Данные по пользователю.
	 *
	 * @return bool
	 */
	public function sendLoginEvent( array $data ): bool {
		return $this->request( 'login', $data );
	}

	/**
	 * Отправка запроса в API.
	 *
	 * @param string $method Название метода.
	 * @param array  $data   Данные по пользователю.
	 *
	 * @return true
	 */
	private function request(string $method, array $data): bool {
		$defaults = [
			'eventTimestamp' => time(),
		];

		$lvId = $this->getLvId();

		if ( $lvId ) {
			$defaults['lvid'] = $lvId;
		}

		$data = array_merge( $defaults, $data );

		try
		{
			$response = $this->client->post(
				sprintf('%s/?idApp=%d', $method, $this->getAppId()),
				[
					'body' => json_encode($data),
					'headers' => [
						'Content-Type'  => 'application/json',
						'Authorization' => $this->getApiKey(),
					]
				]
			);

			$this->log->write($method);
			$this->log->write($data);

			return $response->getStatusCode() === 200;
		} catch ( Exception $e ) {
			$this->log->write($method);
			$this->log->write($e->getMessage());
			$this->log->write($data);

			return false;
		}

	}

	/**
	 * Получает идентификатор устройства пользователя.
	 *
	 * @return string
	 */
	private function getLvId(): string {
		$cookieName = 'mytracker_lvid';

		$lvId = $_COOKIE[ $cookieName ] ?? '';

		if ( $lvId === '' || mb_strlen( $lvId ) !== 32 ) {
			return '';
		}

		return $lvId;
	}
}