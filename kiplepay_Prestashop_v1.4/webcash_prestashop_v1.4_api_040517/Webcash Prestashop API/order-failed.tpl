{capture name=path}{l s='Order confirmation'}{/capture}

{include file=$tpl_dir./breadcrumb.tpl}



<h2>{l s='Order confirmation'}</h2>



{assign var='current_step' value='payment'}

{include file=$tpl_dir./order-steps.tpl}



{include file=$tpl_dir./errors.tpl}



{$HOOK_ORDER_CONFIRMATION}

{$HOOK_PAYMENT_RETURN}

<P><br>
<P>
<p align="center"><font color="#FF0000" size="+2"><strong>Payment Failed</strong></font></p><p><br>
<p align="center"><font face="Verdana, Arial, Helvetica, sans-serif">Payment incomplete. Don't panic.</p>
<p align="center"><font face="Verdana, Arial, Helvetica, sans-serif">Please call Webcash at 03-83188977 for confirmation.</font></p>
<p align="center">&nbsp;</p>
<p align="center">&nbsp;</p>
<p align="center"><font face="Verdana, Arial, Helvetica, sans-serif">- <a href="history.php">MY ORDER HISTORY</a> -</font></p>

<P>

<br />

<a href="{$base_dir_ssl}history.php" title="{l s='Back to orders'}"><img src="{$img_dir}icon/order.gif" alt="{l s='Back to orders'}" class="icon" /></a>

<a href="{$base_dir_ssl}history.php" title="{l s='Back to orders'}">{l s='Back to orders'}</a></a>