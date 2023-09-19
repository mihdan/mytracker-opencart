<?php

/**
 * @link https://webocreation.com/show-module-link-left-menu-admin-opencart-ocmod/
 * @link https://opencart.club/blogs/entry/9-realizaciya-sobytiy-v-opencart-23-3x-4x/
 */
class ControllerExtensionModuleMyTracker extends Controller {
	const PREFIX = 'mytracker';
	private $error = array();

	private $events = [
		[
			'code'		=> 'mytracker_admin_column_left',
			'trigger'	=> 'admin/view/common/column_left/before',
			'action'	=> 'extension/module/mytracker/menus'
		],
		[
			'code'		=> 'mytracker_header',
			'trigger'	=> 'catalog/view/common/header/after',
			'action'	=> 'extension/module/mytracker/code'
		],
		[
			'code'		=> 'mytracker_login',
			'trigger'	=> 'catalog/model/account/customer/deleteLoginAttempts/after',
			'action'	=> 'extension/module/mytracker/onCustomerLogin'
		],
		[
			'code'		=> 'mytracker_registration',
			'trigger'	=> 'catalog/model/account/customer/addCustomer/after',
			'action'	=> 'extension/module/mytracker/onCustomerRegistration'
		],
	];

	public function index() {

		$this->load->model('extension/module/mytracker');
		$this->load->language('extension/module/mytracker');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->model_extension_module_mytracker->saveSettings();
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$this->document->setTitle($this->language->get('doc_title'));

		// сборка данных страницы
		$data = [];

		// Хлебные крошки.
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/mytracker', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['text_entry_status'] = $this->language->get('text_entry_status');

		// Заголовки табов.
		$data['tab_link'] = $this->url->link('extension/module/mytracker', 'user_token=' . $this->session->data['user_token'], true);
		$data['text_tab_general'] = $this->language->get('text_tab_general');
		$data['text_tab_additional'] = $this->language->get('text_tab_additional');
		$data['text_tab_api'] = $this->language->get('text_tab_api');

		$data['text_counter_id'] = $this->language->get('text_counter_id');
		$data['text_domain'] = $this->language->get('text_domain');

		// Статус.
		if (isset($this->request->post['module_mytracker_status'])) {
			$data['module_mytracker_status'] = $this->request->post['module_mytracker_status'];
		} else {
			$data['module_mytracker_status'] = $this->config->get('module_mytracker_status');
		}

		// ID счётчика.
		if (isset($this->request->post['module_mytracker_counter_id'])) {
			$data['module_mytracker_counter_id'] = $this->request->post['module_mytracker_counter_id'];
		} else {
			$data['module_mytracker_counter_id'] = $this->config->get('module_mytracker_counter_id');
		}

		// Домен.
		if (isset($this->request->post['module_mytracker_domain'])) {
			$data['module_mytracker_domain'] = $this->request->post['module_mytracker_domain'];
		} else {
			$data['module_mytracker_domain'] = $this->config->get('module_mytracker_domain');
		}

		// Отслеживать пользователя.
		if (isset($this->request->post['module_mytracker_tracking_user'])) {
			$data['module_mytracker_tracking_user'] = $this->request->post['module_mytracker_tracking_user'];
		} else {
			$data['module_mytracker_tracking_user'] = $this->config->get('module_mytracker_tracking_user');
		}

		// Поддержка АМР.
		if (isset($this->request->post['module_mytracker_amp_support'])) {
			$data['module_mytracker_amp_support'] = $this->request->post['module_mytracker_amp_support'];
		} else {
			$data['module_mytracker_amp_support'] = $this->config->get('module_mytracker_amp_support');
		}

		// App ID.
		if (isset($this->request->post['module_mytracker_app_id'])) {
			$data['module_mytracker_app_id'] = $this->request->post['module_mytracker_app_id'];
		} else {
			$data['module_mytracker_app_id'] = $this->config->get('module_mytracker_app_id');
		}

		// API Key.
		if (isset($this->request->post['module_mytracker_api_key'])) {
			$data['module_mytracker_api_key'] = $this->request->post['module_mytracker_api_key'];
		} else {
			$data['module_mytracker_api_key'] = $this->config->get('module_mytracker_api_key');
		}

		// Отслеживать входы.
		if (isset($this->request->post['module_mytracker_tracking_login'])) {
			$data['module_mytracker_tracking_login'] = $this->request->post['module_mytracker_tracking_login'];
		} else {
			$data['module_mytracker_tracking_login'] = $this->config->get('module_mytracker_tracking_login');
		}

		// Отслеживать регистрации.
		if (isset($this->request->post['module_mytracker_tracking_registration'])) {
			$data['module_mytracker_tracking_registration'] = $this->request->post['module_mytracker_tracking_registration'];
		} else {
			$data['module_mytracker_tracking_registration'] = $this->config->get('module_mytracker_tracking_registration');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');


		$this->response->setOutput($this->load->view('extension/module/mytracker', $data));
	}

	public function install() {
		$this->load->model('extension/module/mytracker');
		$this->checkEvent();
	}

	/**
	 * Удаление плагина.
	 *
	 * @return void
	 */
	public function uninstall() {
		$this->load->model('extension/module/mytracker');
		$this->removeEvent();

		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting(self::PREFIX);
	}

	private function checkEvent() {
		$this->load->model('setting/event');

		foreach($this->events as $event) {
			if(!$result = $this->model_setting_event->getEventByCode($event['code'])) {
				$this->model_setting_event->addEvent($event['code'], $event['trigger'], $event['action']);
			}
		}
	}

	private function removeEvent() {
		$this->load->model('setting/event');

		foreach($this->events as $event) {
			$this->model_setting_event->deleteEventByCode($event['code']);
		}
	}

	protected function validate() {}

	public function menus(&$route, &$data, &$output) {

		if (!$this->config->get('module_mytracker_status')) {
			return null;
		}

		$this->load->language('extension/module/mytracker');

		//print_r($data);die;

		//if ($this->user->hasPermission('access', 'extension/module/mytracker')) {
			$num = -1;
			foreach($data['menus'] as $menus) {
				$num ++;
				foreach ($menus as $key => $value) {
					if($value=='menu-marketing'){
						$data['menus'][$num]['children'][] = array(
							'name'     => $this->language->get('heading_title'),
							'href'     => $this->url->link('extension/module/mytracker', 'user_token=' . $this->session->data['user_token'], true),
							'children' => array()
						);
					};
				}

			}

		//}
	}
}