<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<style type="text/css">
.style1 {
	font-size: medium;
	font-family: "Times New Roman", Times, serif;
}
.style2 {
	font-size: large;
	font-family: "Times New Roman", Times, serif;
}
.style3 {
	font-family: "Alfredo's Dance";
	font-size: x-large;
}
.style4 {
	font-size: large;
	font-family: "Bookman Old Style";
}
</style>
</head>
<p>{*<br />
* 2007-2012 PrestaShop<br />
*<br />
* NOTICE OF LICENSE<br />
*<br />
* This source file is subject to the Academic Free License (AFL 3.0)<br />
* that is bundled with this package in the file LICENSE.txt.<br />
* It is also available through the world-wide-web at this URL:<br />
* http://opensource.org/licenses/afl-3.0.php<br />
* If you did not receive a copy of the license and are unable to<br />
* obtain it through the world-wide-web, please send an email<br />
* to license@prestashop.com so we can send you a copy immediately.<br />
*<br />
* DISCLAIMER<br />
*<br />
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer<br />
* versions in the future. If you wish to customize PrestaShop for your<br />
* needs please refer to http://www.prestashop.com for more information.<br />
*<br />
*  @author PrestaShop SA &lt;contact@prestashop.com&gt;<br />
*  @copyright  2007-2012 PrestaShop SA<br />
*  @version  Release: $Revision: 7465 $<br />
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)<br />
*  International Registered Trademark &amp; Property of PrestaShop SA<br />
*}

<br />
<font color="#FF3300" class="style1">{$dat}</font>
{if $dat =='2'}
<p align="left" class="style3">Terima Kasih</p><br>
<p align="center"> <span class="style1">Pembayaran Anda untuk Transaksi ID : </span><font color="#FF3300" class="style1">{$trx}</font>
<strong><span class="style4">SUKSES</span></strong> </p>
{else}
<p align="left" class="style3">MAAF</p><br>
<p align="center"><span class="style1"> Pembayaran Anda untuk Transaksi ID : </span><font color="#FF3300" class="style1"> {$trx}</font><strong><span class="style2"> GAGAL </span></strong> </p>
{/if}

