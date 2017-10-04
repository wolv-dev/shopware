{extends file='frontend/checkout/cart_item_premium_product.tpl'}

{* Premium tax price *}
{block name='frontend_checkout_cart_item_premium_tax_price'}
    <div class="panel--td column--tax-price block is--align-right">
        {block name='frontend_checkout_cart_voucher_tax_label'}
            <div class="column--label tax-price--label">
                {if $sUserData.additional.charge_vat && !$sUserData.additional.show_gross}
                    {s name='CheckoutColumnExcludeTax' namespace="frontend/checkout/confirm_header"}{/s}
                {elseif $sUserData.additional.charge_vat}
                    {s name='CheckoutColumnTax' namespace="frontend/checkout/confirm_header"}{/s}
                {/if}
            </div>
        {/block}

        {if $sUserData.additional.charge_vat}{$sBasketItem.tax|currency}{else}&nbsp;{/if}
    </div>
{/block}
