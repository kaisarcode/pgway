<?php
/**
* 2007 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    KaisarCode <info@kaisarcode.com>
*  @copyright 2021 KaisarCode
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class PGWay extends PaymentModule
{
    
    private $hooks = array(
        'payment',
        'paymentReturn',
        'paymentOptions',
        'displayBackofficeHeader'
    );
    
    public function __construct()
    {
        $this->name = 'pgway';
        $this->displayName = $this->l('PGWay');
        $this->description = $this->l('Generic payment gateway module');
        $this->paymentMethodName = $this->l('Payment Gateway');
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'KaisarCode';
        
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->path = realpath(dirname(__FILE__));
        $this->ps_version = Configuration::get('PS_VERSION_DB');
        $this->ps_version = explode('.', $this->ps_version);
        $this->ps_version = $this->ps_version[0].$this->ps_version[1];
        $this->ps_versions_compliancy = array(
            'min' => '1.6',
            'max' => _PS_VERSION_
        );
        
        parent::__construct();
    }
    
    // DATA USED BY MODULE
    public function setUp()
    {
        $data = new stdClass();
        $this->getKeys($data);
        $data->mod = $this;
        $data->lnk = new Link();
        $data->ctx = Context::getContext();
        $data->pth = $this->path;
        $data->url = _PS_BASE_URL_.__PS_BASE_URI__;
        $data->ver = $this->ps_version;
        $data->lng = $data->ctx->language->id;
        $data->iso = $data->ctx->language->iso_code;
        $data->shp = $data->ctx->shop->id;
        $data->ssl = Tools::usingSecureMode();
        $skeyp = ['pgway-srvkey' => $data->skey];
        $data->url = new stdClass();
        $data->url->pay = $data->lnk->getModuleLink($this->name, 'payment', $skeyp, $data->ssl);
        $data->url->srv = $data->lnk->getModuleLink($this->name, 'service', $skeyp, $data->ssl);
        
        $data->url->api_sbox = 'https://sandbox.api.com/v1';
        $data->url->api_live = 'https://api.com/v1';
        $data->url->api_url = $data->api_prd;
        $data->sbx && $data->api_url = $data->api_sbx;
        
        return $data;
    }
    
    // BO HEADER
    public function hookDisplayBackofficeHeader()
    {
        $data = $this->setUp();
        return $this->displayTpl('admin/header', $data);
    }
    
    // CONFIG PAGE
    public function getContent()
    {
        $this->regHooks();
        $data = $this->setUp();
        $this->updateKeys();
        $this->getKeys($data);
        $this->createOrderStates();
        
        // If credentials not set preselect the tab
        $data->tab = Tools::getValue('pgway-tab');
        !$data->tab && (
        !Configuration::get('PGWAY_KEY') ||
        !Configuration::get('PGWAY_TKN') ||
        !Configuration::get('PGWAY_KEY_SBX')||
        !Configuration::get('PGWAY_TKN_SBX'))&&
        $data->tab = 'keys';
        
        // Display config
        return $this->displayTpl('admin/config', $data);
    }
    
    // PAYMENT
    public function hookPayment($prms)
    {
        if (!$this->active) {
            return '';
        }

        $prms = Tools::jsonEncode($prms);
        $prms = Tools::jsonDecode($prms);
        $data = $this->setUp();
        if (!$data->key) {
            return '';
        }
        
        $cart = $this->context->cart;
        $cust = new Customer($cart->id_customer);
        $data->cart = $cart;
        $data->fname = $cust->firstname;
        $data->lname = $cust->lastname;
        $data->total = $cart->getOrderTotal(true, Cart::BOTH);
        $curr = new Currency($cart->id_currency);
        $data->curs = $curr->sign;
        
        return $this->displayTpl('front/options', $data);
    }
    
    // PAYMENT OPTIONS
    public function hookPaymentOptions($prms)
    {
        if (!$this->active) {
            return [];
        }

        $prms = Tools::jsonEncode($prms);
        $prms = Tools::jsonDecode($prms);
        $data = $this->setUp();
        if (!$data->key) {
            return [];
        }
        
        $cart = $this->context->cart;
        $cust = new Customer($cart->id_customer);
        $data->cart = $cart;
        $data->fname = $cust->firstname;
        $data->lname = $cust->lastname;
        $data->total = $cart->getOrderTotal(true, Cart::BOTH);
        $curr = new Currency($cart->id_currency);
        $data->curs = $curr->sign;
        
        $dnam = $data->ttl;
        $link = $data->url->pay;
        $tmpl = $this->displayTpl('front/options', $data);
        $option = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $option->setCallToActionText($dnam);
        $option->setAction($link);
        $option->setForm($tmpl);
        
        return [$option];
    }
    
    // PAYMENT RETURN
    public function hookPaymentReturn($prms)
    {
        if (!$this->active) {
            return '';
        }
        $prms = Tools::jsonEncode($prms);
        $prms = Tools::jsonDecode($prms);
        $data = $this->setUp();
        if ($data->ver == 16) {
            return $this->displayTpl('front/success', $data);
        }
    }
    
    // DISPLAY TEMPLATE
    public function displayTpl($tpl, $data = null)
    {
        $name = $this->name;
        $this->context->smarty->assign('data', $data);
        if ($this->ps_version < 17) {
            return $this->display(__FILE__, "/views/templates/$tpl.tpl");
        } else {
            return $this->fetch("module:$name/views/templates/$tpl.tpl");
        }
    }
    
    // INSTALL MODULE
    public function install()
    {
        parent::install();
        $this->regHooks();
        $this->installTabs();
        Configuration::updateValue('PGWAY_SBX', 1);
        include "{$this->path}/includes/install.php";
        $this->createOrderStates();
        return true;
    }
    
    // UNINSTALL MODULE
    public function uninstall()
    {
        parent::uninstall();
        $this->uninstallTabs();
        $this->deleteOrderStates();
        include "{$this->path}/includes/uninstall.php";
        $this->deleteKeys();
        return true;
    }
    
    // REGISTER HOOKS
    private function regHooks()
    {
        foreach ($this->hooks as $hook) {
            if (!$this->isRegisteredInHook($hook)) {
                $this->registerHook($hook);
            }
        }
    }
    
    // INSTALL TABS
    public function installTabs()
    {
        $this->addTab($this->l('PGWay'));
        $this->addTab($this->l('Dummy'), 'Dummy');
    }
    
    // UNINSTALL TABS
    public function uninstallTabs()
    {
        $dbx = _DB_PREFIX_;
        $sql = "
        SELECT id_tab FROM {$dbx}tab
        WHERE module = '{$this->name}'";
        $tabs = Db::getInstance()->executeS($sql);
        foreach ($tabs as $t) {
            $idt = $t['id_tab'];
            $tab = new Tab($idt);
            $tab->delete();
        }
    }
    
    // ADD MENU TAB
    public function addTab($txt, $cls = '')
    {
        $tnm = 'AdminPGWay';
        $pid = Tab::getIdFromClassName($tnm);
        $tid = Tab::getIdFromClassName($tnm.$cls);
        $lns = Language::getLanguages(false);
        if (!$tid) {
            $tab = new Tab();
            $tab->class_name = $tnm.$cls;
            $tab->module = $this->name;
            $tab->id_parent = 0;
            $cls && $tab->id_parent = $pid;
            $cls && $tab->icon = 'settings';
            foreach($lns as $ln){
                $tab->name[$ln['id_lang']] = $txt;
            }
            $tab->save();
        }
    }
    
    // UPDATE CONFIG KEYS
    private function updateKeys()
    {
        // Sandbox mode
        if (Tools::getIsset('pgway-sbx')) {
            $sbx = trim(Tools::getValue('pgway-sbx'));
            Configuration::updateValue('PGWAY_SBX', $sbx);
        }
        
        // Production public key
        if (Tools::getIsset('pgway-key')) {
            $key = trim(Tools::getValue('pgway-key'));
            Configuration::updateValue('PGWAY_KEY', $key);
        }
        
        // Production private key
        if (Tools::getIsset('pgway-tkn')) {
            $tkn = trim(Tools::getValue('pgway-tkn'));
            Configuration::updateValue('PGWAY_TKN', $tkn);
        }
        
        // Sandbox public key
        if (Tools::getIsset('pgway-key-sbx')) {
            $key = trim(Tools::getValue('pgway-key-sbx'));
            Configuration::updateValue('PGWAY_KEY_SBX', $key);
        }
        
        // Sandbox private key
        if (Tools::getIsset('pgway-tkn-sbx')) {
            $tkn = trim(Tools::getValue('pgway-tkn-sbx'));
            Configuration::updateValue('PGWAY_TKN_SBX', $tkn);
        }
        
        // Payment option title
        if (Tools::getIsset('pgway-ttl')) {
            $nos = trim(Tools::getValue('pgway-ttl'));
            Configuration::updateValue('PGWAY_TTL', $nos);
        }
    }
    
    // GET CONFIG KEYS
    private function getKeys($data)
    {
        $data->sbx = (int) Configuration::get('PGWAY_SBX');
        $data->key_prd = Configuration::get('PGWAY_KEY');
        $data->tkn_prd = Configuration::get('PGWAY_TKN');
        $data->key_sbx = Configuration::get('PGWAY_KEY_SBX');
        $data->tkn_sbx = Configuration::get('PGWAY_TKN_SBX');
        $data->key = $data->key_prd;
        $data->tkn = $data->tkn_prd;
        if ($data->sbx) {
            $data->key = $data->key_sbx;
            $data->tkn = $data->tkn_sbx;
        }
        $data->ttl = Configuration::get('PGWAY_TTL');
        if (!$data->ttl) {
            $data->ttl = $this->paymentMethodName;
        }
        
        // Obtain internal service key
        $data->skey = Configuration::get('PGWAY_SRV_KEY');
        if (!$data->skey) {
            $data->skey = bin2hex(time().uniqid());
            Configuration::updateValue('PGWAY_SRV_KEY', $data->skey);
        }
    }
    
    // DELETE CONFIG KEYS
    private function deleteKeys()
    {
        Configuration::deleteByName('PGWAY_SBX');
        Configuration::deleteByName('PGWAY_KEY');
        Configuration::deleteByName('PGWAY_KEY_SBX');
        Configuration::deleteByName('PGWAY_TKN');
        Configuration::deleteByName('PGWAY_TKN_SBX');
        Configuration::deleteByName('PGWAY_TTL');
    }
    
    // GET ORDER STATES
    public function getOrderStates()
    {
        $data = $this->setUp();
        $dbx = _DB_PREFIX_;
        $sql = "
        SELECT
            id_order_state,
            pgway_state
        FROM {$dbx}order_state
        WHERE module_name = 'pgway';";
        $res = Db::getInstance()->executeS($sql);
        $res = Tools::jsonEncode($res);
        $res = Tools::jsonDecode($res);
        return $res;
    }
    
    // GET ORDER STATE
    public function getOrderState($id)
    {
        $ost = 0;
        $osts = $this->getOrderStates();
        foreach ($osts as $os) {
            if ($os->pgway_state == $id) {
                $ost = $os->id_order_state;
            }
        }
        return (int) $ost;  
    }
    
    // CREATE ORDER STATES
    public function createOrderStates()
    {
        $sts = array();
        foreach ($this->getOrderStates() as $ost) {
            $i = $ost->id_order_state;
            $s = $ost->pgway_state;
            $o = new OrderState($i);
            $o->unremovable = 1;
            $o->deleted = 0;
            $o->save();
            array_push($sts, $s);
        }
        if (!in_array('approved', $sts)) {
            $this->createOrderState('approved', 'PGWay - Approved', true);
        }
        if (!in_array('rejected', $sts)) {
            $this->createOrderState('rejected', 'PGWay - Rejected', true);
        }
    }
    
    // CREATE ORDER STATE
    private function createOrderState($id, $name, $paid = false)
    {
        $data = $this->setUp();
        $dbx = _DB_PREFIX_;
        $state = new OrderState();
        $state->name = array();
        foreach (Language::getLanguages() as $lang) {
            $l = $lang['id_lang'];
            $state->name[$l] = $name;
        }
        $state->module_name = 'pgway';
        $state->color = '#008080';
        $state->unremovable = 1;
        $state->paid = $paid;
        $dir = _PS_ROOT_DIR_;
        if ($state->add()) {
            $file = $dir.'/img/os/'.$state->id.'.gif';
            copy($data->pth.'/logo.gif', $file);
        }
        $sql = "
        UPDATE {$dbx}order_state
        SET pgway_state = '$id'
        WHERE id_order_state = {$state->id};";
        Db::getInstance()->execute($sql);
    }
    
    // DELETE ORDER STATES
    public function deleteOrderStates()
    {
        foreach ($this->getOrderStates() as $ost) {
            $s = $ost->id_order_state;
            $ost = new OrderState($s);
            $ost->deleted = 1;
            $ost->save();
        }
    }
    
    // API REQUESTS
    public function callAPI($ept = '', $prm = array())
    {
        $data = $this->setUp();
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $data->api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => Tools::jsonEncode($prm),
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "Accept: application/json",
                "content-type: application/json",
                "Authorization: Bearer {$data->tkn}"
            ),
        ));
        $res = curl_exec($curl);
        $res = Tools::jsonDecode($res);
        curl_close($curl);
        return $res;
    }
}
