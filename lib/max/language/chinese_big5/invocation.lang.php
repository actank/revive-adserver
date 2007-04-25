<?php

/*
+---------------------------------------------------------------------------+
| Openads v2.3                                                              |
| =============                                                             |
|                                                                           |
| Copyright (c) 2003-2007 Openads Ltd                                       |
| For contact details, see: http://www.openads.org/                         |
|                                                                           |
| Copyright (c) 2000-2003 the phpAdsNew developers                          |
| For contact details, see: http://www.phpadsnew.com/                       |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id$
*/



// Invocation Types
$GLOBALS['strInvocationRemote']			= "遠程調用";
$GLOBALS['strInvocationJS']			= "遠程調用Javascript";
$GLOBALS['strInvocationIframes']		= "遠程調用Frames";
$GLOBALS['strInvocationXmlRpc']			= "遠程調用XML-RPC";
$GLOBALS['strInvocationCombined']		= "組合遠程調用";
$GLOBALS['strInvocationPopUp']			= "彈出";
$GLOBALS['strInvocationAdLayer']		= "空隙或者漂浮的DHTML";
$GLOBALS['strInvocationLocal']			= "本地模式";


// Other
$GLOBALS['strCopyToClipboard']			= "複製到剪切版";


// Measures
$GLOBALS['strAbbrPixels']			= "象素";
$GLOBALS['strAbbrSeconds']			= "秒";


// Common Invocation Parameters
$GLOBALS['strInvocationWhat']			= "選擇廣告";
$GLOBALS['strInvocationClientID']		= "客戶或項目";
$GLOBALS['strInvocationTarget']			= "目標Frame";
$GLOBALS['strInvocationSource']			= "來源";
$GLOBALS['strInvocationWithText']		= "在廣告下面顯示文字";
$GLOBALS['strInvocationDontShowAgain']		= "在同一頁面不再顯示此廣告";
$GLOBALS['strInvocationDontShowAgainCampaign']	= "在同一頁面不再顯示此項目的廣告";
$GLOBALS['strInvocationTemplate'] 		= "把廣告保存在一個變量中，可以在一個模板裡面使用";


// Iframe
$GLOBALS['strIFrameRefreshAfter']		= "刷新時間";
$GLOBALS['strIframeResizeToBanner']		= "使iframe與廣告的尺寸保持一致";
$GLOBALS['strIframeMakeTransparent']		= "使iframe透明";
$GLOBALS['strIframeIncludeNetscape4']		= "包括Netscape 4兼容的ilayer";


// PopUp
$GLOBALS['strPopUpStyle']			= "彈出類型";
$GLOBALS['strPopUpStylePopUp']			= "彈出";
$GLOBALS['strPopUpStylePopUnder']		= "彈下";
$GLOBALS['strPopUpCreateInstance']		= "創建彈出式廣告的情況";
$GLOBALS['strPopUpImmediately']			= "立即";
$GLOBALS['strPopUpOnClose']			= "當此頁面關閉時";
$GLOBALS['strPopUpAfterSec']			= "時間間隔";
$GLOBALS['strAutoCloseAfter']			= "自動關閉時間";
$GLOBALS['strPopUpTop']				= "起始位置(上)";
$GLOBALS['strPopUpLeft']			= "起始位置(左)";


// XML-RPC
$GLOBALS['strXmlRpcLanguage']			= "主機語言";


// AdLayer
$GLOBALS['strAdLayerStyle']			= "風格";

$GLOBALS['strAlignment']			= "對齊";
$GLOBALS['strHAlignment']			= "橫向對齊";
$GLOBALS['strLeft']				= "左";
$GLOBALS['strCenter']				= "中";
$GLOBALS['strRight']				= "右";

$GLOBALS['strVAlignment']			= "縱向對齊";
$GLOBALS['strTop']				= "頂部";
$GLOBALS['strMiddle']				= "中部";
$GLOBALS['strBottom']				= "底部";

$GLOBALS['strAutoCollapseAfter']		= "自動折疊時間";
$GLOBALS['strCloseText']			= "關閉文字";
$GLOBALS['strClose']				= "[關閉]";
$GLOBALS['strBannerPadding']			= "廣告補白";

$GLOBALS['strHShift']				= "橫向移動";
$GLOBALS['strVShift']				= "縱向移動";

$GLOBALS['strShowCloseButton']			= "顯示關閉按鈕";
$GLOBALS['strBackgroundColor']			= "背景色";
$GLOBALS['strBorderColor']			= "邊框顏色";

$GLOBALS['strDirection']			= "方向";
$GLOBALS['strLeftToRight']			= "從左到右";
$GLOBALS['strRightToLeft']			= "從右到左";
$GLOBALS['strLooping']				= "循環";
$GLOBALS['strAlwaysActive']			= "總是啟用";
$GLOBALS['strSpeed']				= "速度";
$GLOBALS['strPause']				= "暫停";
$GLOBALS['strLimited']				= "限制";
$GLOBALS['strLeftMargin']			= "左邊界";
$GLOBALS['strRightMargin']			= "右邊界";
$GLOBALS['strTransparentBackground']		= "透明背景";

$GLOBALS['strSmoothMovement']			= "平滑移動";
$GLOBALS['strHideNotMoving']			= "鼠標不移動時隱藏廣告";
$GLOBALS['strHideDelay']			= "隱藏廣告前的時間延遲";
$GLOBALS['strHideTransparancy']			= "使隱藏的廣告透明";


$GLOBALS['strAdLayerStyleName']	= array(
	'geocities'		=> "地面",
	'simple'		=> "廣告",
	'cursor'		=> "鼠標",
	'floater'		=> "漂浮"
);

?>