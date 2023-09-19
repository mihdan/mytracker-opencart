<?php
class ModelExtensionModuleMyTracker extends Model {
	public function saveSettings() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('module_mytracker', $this->request->post);
	}
}