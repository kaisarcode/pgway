{**
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
*}

<form id="pgway-form" action="{$data->url->pay|escape:'htmlall':'UTF-8'}" method="post">
    <div class="pgway-panel">
        <h3>{$data->ttl|escape:'htmlall':'UTF-8'}</h3>
        <hr {if ($data->ver == 17)}style="margin: 10px 0;"{/if}>
        <div {if ($data->ver < 17)}class="row"{/if}>
            <div class="col-12 {if ($data->ver < 17)}col-lg-6{/if}">
                
                <!-- HOLDER NAME -->
                <div class="row">
                    <div class="col-12 col-lg-12">
                        <div class="form-group">
                            <label for="pgway-holder">{l s='Holder name' mod='pgway'}</label>
                            <input type="text" id="pgway-holder" name="pgway-holder"
                            class="form-control" value="{$data->fname} {$data->lname}"
                            placeholder="{l s='John Doe' mod='pgway'}" required>
                        </div>
                    </div>
                </div>
                
                <!-- DOC TYPE AND NUMBER-->
                <div class="row">
                    <div class="col-12 col-lg-3">
                        <div class="form-group">
                            <label for="pgway-doc-type">{l s='Document type' mod='pgway'}</label>
                            <select id="pgway-doc-type" name="pgway-doc-type"
                            class="form-control" required>
                                <option value="dni">DNI</option>
                                <option value="cuil">CUIL</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-lg-9">
                        <div class="form-group">
                            <label for="pgway-doc-number">{l s='Document number' mod='pgway'}</label>
                            <input type="text" id="pgway-doc-number" name="pgway-doc-number"
                            class="form-control" value="" placeholder="********" required>
                        </div>
                    </div>
                </div>
                
                <!-- CARD NUMBER -->
                <div class="row">
                    <div class="col-12 col-lg-12">
                        <div class="form-group">
                            <label for="pgway-card-number">{l s='Card number' mod='pgway'}</label>
                            <input type="text" id="pgway-card-number" class="form-control"
                            value="" placeholder="**** **** **** ****" maxlength="24" required>
                            <input type="hidden" name="pgway-card-number" value="">
                        </div>
                    </div>
                </div>
                
                <!-- CARD AND ISSUER -->
                <div class="row">
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="pgway-card-id">{l s='Card brand' mod='pgway'}</label>
                            <select id="pgway-card-id" name="pgway-card-id"
                            class="form-control" required>
                                <option value="1">VISA</option>
                            </select>
                        </div>
                        <input type="hidden" id="pgway-card-name" name="pgway-card-name" value="">
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="pgway-issuer-id">{l s='Issuer' mod='pgway'}</label>
                            <select id="pgway-issuer-id" name="pgway-issuer-id"
                            class="form-control" required>
                                <option value="1">Test bank</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- EXPIRATION DATE AND SECURITY CODE -->
                <div class="row">
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="pgway-card-expir">{l s='Expiration date' mod='pgway'}</label>
                            <input type="text" id="pgway-card-expir" class="form-control" name="pgway-card-expir"
                            value="" placeholder="{l s='MM/YY' mod='pgway'}" maxlength="5" required>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="pgway-cvc">{l s='Card verification number' mod='pgway'}</label>
                            <input id="pgway-cvc" name="pgway-cvc"
                            class="form-control" type="text" value=""
                            placeholder="***" maxlength="4" required>
                        </div>
                    </div>
                    <input type="hidden" name="pgway-card-expir-m" value="">
                    <input type="hidden" name="pgway-card-expir-y" value="">
                </div>
                
                <!-- INSTALLMENTS -->
                <div class="row">
                    <div class="col-12 col-lg-12">
                        <div class="form-group">
                            <label for="pgway-installments">{l s='Installments' mod='pgway'}</label>
                            <select id="pgway-installments" name="pgway-installments"
                            class="form-control" required>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                            <input type="hidden" id="pgway-ins-total" name="pgway-ins-total">
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
        {if ($data->ver == 16)}
        <!-- SUBMIT -->
        <div>
            <button type="submit"
            class="btn btn-primary">
                {l s='Checkout' mod='pgway'}
            </button>
        </div>
        {/if}
        
    </div>
</form>

<script>
if (document.readyState != 'loading'){
    setOptionPGWay();
} else {
    document.addEventListener
    ('DOMContentLoaded', setOptionPGWay);
}
function setOptionPGWay() {
    var srv = '{$data->url->srv nofilter}';
    var cur = '{$data->curs}';
    setTimeout(function(){
        
        // Manage doc number
        function docNumber() {
            var ipt = $('#pgway-doc-number');
            function mask() {
                var el = $(this);
                $(el).val($(el).val().replace(/\s/g, ''));
                $(el).val($(el).val().replace(/-/g, ''));
                $(el).val($(el).val().replace(/\./g, ''));
                $(el).val($(el).val().replace(/[a-zA-Z]/g, ''));
                $(el).val($(el).val().trim());
            }
            ipt.on('keyup', mask);
            ipt.on('change', mask);
            ipt.trigger('change');
        } docNumber();
        
        // Manage card number
        function cardNumber() {
            var ipt = $('#pgway-card-number');
            function mask() {
                var el = $(this);
                $(el).val($(el).val().replace(/\s/g, ''));
                $(el).val($(el).val().replace(/-/g, ''));
                $(el).val($(el).val().replace(/\./g, ''));
                $(el).val($(el).val().replace(/[a-zA-Z]/g, ''));
                var chr = $(el).val().split('');
                if (chr.length > 4) chr.splice(4, 0, ' ');
                if (chr.length > 8) chr.splice(9, 0, ' ');
                if (chr.length > 12) chr.splice(14, 0, ' ');
                if (chr.length > 16) chr.splice(19, 0, ' ');
                var val = chr.join('').trim();
                $(el).val(val);
                val = val.replace(/\s+/g, '');
                $('[name="pgway-card-number"]').val(val);
            }
            ipt.on('keyup', mask);
            ipt.on('change', mask);
            ipt.trigger('change');
        } cardNumber();
        
        // Manage card type
        function cardType() {
            var ipt = $('#pgway-card-id');
            ipt.on('change', function(){
                var txt = ipt.find('option:selected').text();
                $('#pgway-card-name').val(txt);
            }); ipt.trigger('change');
        } cardType();
        
        // Manage card expiration
        function cardExpiration() {
            var ipt = $('#pgway-card-expir');
            function mask() {
                var el = $(this);
                $(el).val($(el).val().replace(/-/g, ''));
                $(el).val($(el).val().replace(/\//g, ''));
                $(el).val($(el).val().replace(/[a-zA-Z]/g, ''));
                var chr = $(el).val().split('');
                if (chr.length > 2) {
                    chr.splice(2, 0, '/');
                }
                var val = chr.join('').trim();
                $(el).val(val);
                val = val.split('/');
                $('[name="pgway-card-expir-m"]').val(val[0]||'');
                $('[name="pgway-card-expir-y"]').val(val[1]||'');
            }
            ipt.on('keyup', mask);
            ipt.on('change', mask);
            ipt.trigger('change');
        } cardExpiration();
        
        // Manage CVC
        function cardCVC() {
            var ipt = $('#pgway-cvv');
            function mask() {
                var el = $(this);
                $(el).val($(el).val().replace(/\s/g, ''));
                $(el).val($(el).val().replace(/[a-zA-Z]/g, ''));
            }
            ipt.on('keyup', mask);
            ipt.on('change', mask);
            ipt.trigger('change');
        } cardCVC();
        
        // VALIDATE AND SUBMIT
        var valid = false;
        var form = document.forms["pgway-form"];
        form.onsubmit = function() {
            
            // Perform async validation
            valid = true;
            
            if (!valid) {
                jQuery('#payment-confirmation button').prop('disabled', 1);
                return false;
            }
        }
        
    },0);
}
</script>

<style>
.pgway-panel {
    border: 1px solid #cccccc;
    border-radius: 4px;
    padding: 25px 20px 20px;
    margin: 0 0 10px;
}
.pgway-panel h3 {
    margin-top: 0;
    color: #222222;
    font-size: 18px;
    font-weight: bold; 
    text-transform: uppercase;
}
#pgway-form input,
#pgway-form select {
    height: auto;
    font-size: 12px;
    padding: 6px 8px;
    border-radius: 3px;
    background-color: #fff;
}
#pgway-form input:focus,
#pgway-form select:focus {
    outline: 1px solid #2fb5d2;
}
#pgway-form select {
    padding: 6px 3px;
}
#pgway-form label {
    font-size: 12px;
}
</style>
