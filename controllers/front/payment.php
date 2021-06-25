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

class PGWayPaymentModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    
    public function initContent()
    {
        parent::initContent();
        $dbx = _DB_PREFIX_;
        $data = $this->module->setUp();
        $smrt = $this->context->smarty;
        $this->module->createOrderStates();
        $cart = $this->context->cart;
        $prms = Tools::getAllValues();
        
        if ($cart->orderExists()) {
            $this->loadPageTemplate('front', 'exists', $data);
        } else {
            $modu = $this->module;
            $shop = new Shop($data->shp);
            $cust = new Customer($cart->id_customer);
            $curr = new Currency($cart->id_currency);
            $carr = new Carrier($cart->id_carrier);
            
            // Billing address
            $bill_addr = new Address($cart->id_address_invoice);
            $bill_ctry = new Country($bill_addr->id_country);
            $bill_stat = new State($bill_addr->id_state);
            
            // Shipping address
            $ship_addr = new Address($cart->id_address_delivery);
            $ship_ctry = new Country($ship_addr->id_country);
            $ship_stat = new State($ship_addr->id_state);
            
            // Order data
            $total = $cart->getOrderTotal(true, Cart::BOTH);
            $refer = Order::generateReference();
            
            // Form data
            $data->holder = Tools::getValue('pgway-holder');
            $data->doctyp = Tools::getValue('pgway-doc-type');
            $data->docnum = Tools::getValue('pgway-doc-number');
            $data->crdnum = Tools::getValue('pgway-card-number');
            $data->cardid = Tools::getValue('pgway-card-id');
            $data->cardnm = Tools::getValue('pgway-card-name');
            $data->bankid = Tools::getValue('pgway-issuer-id');
            $data->cardex = Tools::getValue('pgway-card-expir');
            $data->cardexy = Tools::getValue('pgway-card-expir-y');
            $data->cardexm = Tools::getValue('pgway-card-expir-m');
            $data->cvc = Tools::getValue('pgway-cvc');
            $data->instot = Tools::getValue('pgway-ins-total');
            
            // PGWAY PAYMENT
            $pmnt = array();
            $pmnt->total = $total;
            $res = $modu->callAPI('payments', $pmnt);
            $data->res = $res;
            
            // PS ORDER
            if (isset($res->id)) {
                $status = $res->status;
                
                // Process PS data
                if ($status == 'approved') {
                    
                    // Get order status
                    $ost = $modu->getOrderState($status);
                    
                    // Generate order
                    $modu->validateOrder($cart->id, $ost, $total, $data->ttl, null, [], null, false, $cart->secure_key);
                    $oid = Order::getOrderByCartId($cart->id);
                    $ord = new Order($oid);
                    $ord->reference = $refer;
                    $ord->save();
                    
                    // Generate payment
                    $pay = new OrderPayment();
                    $pay->order_reference = $ord->reference;
                    $pay->id_currency = $curr->id;
                    $pay->amount = $total;
                    $pay->payment_method = $data->ttl;
                    $pay->transaction_id = $res->id;
                    $pay->card_brand = $data->cardnm;
                    $pay->card_expiration = $data->cardex;
                    $pay->card_holder = $data->holder;
                    $pay->save();
                    
                    // Show approved view
                    if ($status == 'approved') {
                        //$this->loadPageTemplate('front', 'response', $data);
                        $url = 'index.php?controller=order-confirmation';
                        $url .= '&id_cart='.$cart->id;
                        $url .= '&id_module='.$modu->id;
                        $url .= '&id_order='.$oid;
                        $url .= '&key='.$cust->secure_key;
                        Tools::redirect($url);
                    }
                    
                } else if ($status == 'rejected') {
                    $this->loadPageTemplate('front', 'rejected', $data);
                } else {
                    $this->loadPageTemplate('front', 'error', $data);
                }
            } else {
                $this->loadPageTemplate('front', 'error', $data);
            }
        }
    }
    
    // LOAD PAGE TEMPLATE
    public function loadPageTemplate($ctrl, $tmpl, $data = null)
    {
        $modu = $this->module;
        $name = $modu->name;
        $smrt = $this->context->smarty;
        $smrt->assign('data', $data);
        if ($modu->ps_version < 17) {
            $this->setTemplate("$tmpl.tpl");
        } else {
            $base = "module:$name/views/templates";
            $smrt->assign('tmpl', "$base/$ctrl/$tmpl.tpl");
            $this->setTemplate("$base/include.tpl");
        }
    }
}
