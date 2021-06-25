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

{if ($data->tab)}
    {assign var="tab" value=$data->tab}
{else}
    {assign var="tab" value='opts'}
{/if}
<form action="" method="post">
    <input type="hidden" name="pgway-tab" value="{$tab}">
    
    <!-- TABS -->
    <ul class="pgway-tabs nav nav-tabs">
        <li {if ($tab == 'crds')}class="active"{/if} data-sel="spl">
            <a data-toggle="tab" href="#pgway-tab-crds">{l s='Credentials' mod='pgway'}</a>
        </li>
        <li {if ($tab == 'opts')}class="active"{/if} data-sel="grp">
            <a data-toggle="tab" href="#pgway-tab-opts">{l s='Options' mod='pgway'}</a>
        </li>
    </ul>
    
    <!-- TAB PANELS -->
    <div class="pgway-tab-panel tab-content panel panel-default form-horizontal">
        
        <!-- CREDENTIALS -->
        <div id="pgway-tab-crds" class="tab-pane fade in
        {if ($tab == 'crds')}active{/if}">
            <div class="form-wrapper">
                
                <!-- Sandbox mode -->
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Sandbox' mod='pgway'}</label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input id="pgway-sbx-on" type="radio" name="pgway-sbx" value="1" {if $data->sbx}checked{/if}>
                            <label for="pgway-sbx-on">{l s='Yes' mod='pgway'}</label>
                            <input id="pgway-sbx-off" type="radio" name="pgway-sbx" value="0" {if !$data->sbx}checked{/if}>
                            <label for="pgway-sbx-off">{l s='No' mod='pgway'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>
                
                <!-- Keys Production -->
                <div class="pgway-sbx-off" {if $data->sbx}style="display:none;"{/if}>
                    <div class="form-group">
                        <label class="control-label col-lg-3">{l s='Access key' mod='pgway'}</label>
                        <div class="col-lg-8">
                            <input type="text" name="pgway-key" value="{$data->key_prd}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-3">{l s='Access token' mod='pgway'}</label>
                        <div class="col-lg-8">
                            <input type="password" name="pgway-tkn" value="{$data->tkn_prd}">
                        </div>
                    </div>
                </div>
                
                <!-- Keys Sandbox -->
                <div class="pgway-sbx-on" {if !$data->sbx}style="display:none;"{/if}>
                    <div class="form-group">
                        <label class="control-label col-lg-3">{l s='Access key' mod='pgway'} ({l s='Sandbox' mod='pgway'})</label>
                        <div class="col-lg-8">
                            <input type="text" name="pgway-key-sbx" value="{$data->key_sbx}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-3">{l s='Access token' mod='pgway'} ({l s='Sandbox' mod='pgway'})</label>
                        <div class="col-lg-8">
                            <input type="password" name="pgway-tkn-sbx" value="{$data->tkn_sbx}">
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="panel-footer">
                <button
                name="pgway-tab" value="crds"
                type="submit" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i>
                    <span>{l s='Save' mod='pgway'}</span>
                </button>
            </div>
        </div>
        
        <!-- CONFIGURATION -->
        <div id="pgway-tab-opts" class="tab-pane fade in
        {if ($tab == 'opts')}active{/if}">
            <div class="form-wrapper">
                
                <!-- Payment option title -->
                <div class="form-wrapper">
                    <div class="form-group">
                        <label class="control-label col-lg-3">{l s='Payment option title' mod='pgway'}</label>
                        <div class="col-lg-8">
                            <input type="text" name="pgway-ttl" value="{$data->ttl}">
                            <div><small><i>*{l s='Text to display in the checkout process' mod='pgway'}.</i></small></div>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="panel-footer">
                <button
                name="pgway-tab" value="opts"
                type="submit" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i>
                    <span>{l s='Save' mod='pgway'}</span>
                </button>
            </div>
        </div>
    </div>
</form>

<script>
// Prevent form resend
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
$('#pgway-sbx-on').on('change', function(){
    var chk = $(this).is(':checked');
    $('.pgway-sbx-on').show();
    $('.pgway-sbx-off').hide();
});
$('#pgway-sbx-off').on('change', function(){
    var chk = $(this).is(':checked');
    $('.pgway-sbx-on').hide();
    $('.pgway-sbx-off').show();
});
</script>
