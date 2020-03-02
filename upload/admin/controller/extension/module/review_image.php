<?php
class ControllerExtensionModuleReviewImage extends Controller {
	private $error = array();
	
	const DEFAULT_MODULE_SETTINGS = [
	'name' => 'Review in Image',
		'status' => 1 /* Enabled by default*/
	];
	
	public function install() {
		$this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_review_image', self::DEFAULT_MODULE_SETTINGS);
		
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "review_image` (
		  `review_image_id` int(11) NOT NULL AUTO_INCREMENT,
		  `review_id` int(11) NOT NULL,
		  `image_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `mime` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		  `data` blob NOT NULL,
		  `sort_order` int(3) NOT NULL DEFAULT 0,
		  `image_size` int(11) NOT NULL,
		  PRIMARY KEY (review_image_id)
		)");
	}
	
	private function addModule() {
        $this->load->model('setting/module');
        $this->model_setting_module->addModule('review_image', self::DEFAULT_MODULE_SETTINGS);

        return $this->db->getLastId();
    }

	public function index() {
		
		if (!isset($this->request->get['module_id'])) {
			$module_id = $this->addModule();
			$this->response->redirect($this->url->link('extension/module/review_image', '&user_token=' . $this->session->data['user_token'] . '&module_id=' . $module_id));
		} else {
			$this->editModule($this->request->get['module_id']);
		}
	}

	protected function editModule($module_id) {
		$this->load->model('setting/module');
		$this->load->language('extension/module/review_image');
		
		$this->document->setTitle($this->language->get('heading_title'));

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
			$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}
		
		$data = array();

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
			'href' => $this->url->link('extension/module/review_image', 'user_token=' . $this->session->data['user_token'], true)
		);

		$module_setting = $this->model_setting_module->getModule($module_id);
		
		 if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } else {
            $data['name'] = $module_setting['name'];
        }	

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} else {
			$data['status'] = $module_setting['status'];
		} 
		
		$data['action']['cancel'] = $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module');
		$data['action']['save'] = "";

		$data['error'] = $this->error;	
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('extension/module/review_image', $data));
	}

	public function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/review_image')) {
			$this->error['permission'] = true;
			return false;
		}
		
		 if (!utf8_strlen($this->request->post['name'])) {
            $this->error['name'] = true;
        }
				
		return empty($this->error);
	
	}
	
	public function uninstall() {
		$this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_review_image');
		$this->db->query("Drop Table `" . DB_PREFIX . "review_image`");
	}
}
