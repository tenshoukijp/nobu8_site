<?php

require( "index_RPD_contents.php" );

function getQueryParam($query_name) {
	return filter_input(INPUT_GET, $query_name);
}

$urlParamPage = getQueryParam('page');
$orgParamPage = getQueryParam('page');


// デフォルトのページ
if ( $urlParamPage == "" ) {
    $urlParamPage = "nobu_spd_home";
}

if (! array_key_exists($urlParamPage, $content_hash) ) {
    $urlParamPage = "401";
}

if ($_SERVER['HTTP_HOST']=="usr.s602.xrea.com") {
    if (str_contains($_SERVER["REQUEST_URI"], "/xn--9oqr43f8k1a.jp-mod.net")) {
        $OldRequest = $_SERVER["REQUEST_URI"];
        $NewRequest = str_replace("/xn--9oqr43f8k1a.jp-mod.net", "", $OldRequest);
	    $NewUrl = "https://xn--9oqr43f8k1a.jp-mod.net".$NewRequest;
        header( "HTTP/1.1 301 Moved Permanently" );
	    header( "Location: ".$NewUrl);
        exit;
    }
}


// コンテンツのページテンプレート読み込み
$strPageTemplate = file_get_contents($content_hash[$urlParamPage]['html']);

// まずBOMの除去
$strPageTemplate = preg_replace('/^\xEF\xBB\xBF/', '', $strPageTemplate);

// eclipseのオートフォーマッタが変な所で改行するので対応
$strPageTemplate = preg_replace('/<img\s+src/ms', '<img src', $strPageTemplate);


// widthやheightが無いイメージタグにマッチしたら、widthやheightを入れる。
$strPageTemplate = preg_replace_callback("/(<img src=[\"'])([^\"']+?)([\"'])((\s+class=[\"'][^\"']+?[\"'])?(\s+attr=[\"']noedge[\"'])?(\s+align=[\"'][a-z]+[\"'])?(\s+attr=[\"']noedge[\"'])?>)/",
                                   function ($matches) {
                                       // httpが含まれている。
                                       if (strpos($matches[0],'http') !== false) {
                                           return $matches[0];

                                       // サイト内の画像
                                       } else {
                                           $strTargetImageFileName = "/virtual/usr/public_html/xn--petw8uc11b.jp/" . $matches[2];
										   $strFullPathInfo = pathinfo( $strTargetImageFileName );
										   $strPathInfoDirName = $strFullPathInfo["dirname"];
										   $strPathInfoBaseName = $strFullPathInfo["basename"];
										   $str2xTargetImageFileName = $strPathInfoDirName . "/2x/2x_" . $strPathInfoBaseName;
										   if (file_exists($str2xTargetImageFileName) ) {
											   $timeTargetImageUpdate = filemtime($str2xTargetImageFileName);
											   $strTargetImageUpdate = date("YmdHis", $timeTargetImageUpdate);
											   list($width, $height, $type, $attr) = getimagesize($strTargetImageFileName);
											   $strTargetUrlPath = str_replace( $strPathInfoBaseName, "2x/2x_" . $strPathInfoBaseName, $matches[2]);
											   return $matches[1] . $strTargetUrlPath . "?v=". $strTargetImageUpdate . $matches[3]  . " alt=\"PICTURE\" " . $attr . $matches[4];
										   } else {
											   $timeTargetImageUpdate = filemtime($strTargetImageFileName);
											   $strTargetImageUpdate = date("YmdHis", $timeTargetImageUpdate);
											   list($width, $height, $type, $attr) = getimagesize($strTargetImageFileName);
											   return $matches[1] . $matches[2] . "?v=". $strTargetImageUpdate . $matches[3]  . " alt=\"PICTURE\" " . $attr . $matches[4];
										   }
                                       }
                                   }, $strPageTemplate);


$vsr_array_style  = array( "%(vs2013runtime)s", "%(vs2015runtime)s", "%(vs2017runtime)s", "%(vs2019runtime)s", "%(vs2022runtime)s" );
$vsarray_template = array(
    'https://www.microsoft.com/ja-jp/download/details.aspx?id=40784',
    'https://support.microsoft.com/ja-jp/help/2977003/the-latest-supported-visual-c-downloads',
    'https://support.microsoft.com/ja-jp/help/2977003/the-latest-supported-visual-c-downloads',
    'https://support.microsoft.com/ja-jp/help/2977003/the-latest-supported-visual-c-downloads',
    'https://support.microsoft.com/ja-jp/help/2977003/the-latest-supported-visual-c-downloads'
);
$strPageTemplate = str_replace($vsr_array_style, $vsarray_template, $strPageTemplate);



$strShCoreHeader = "";
$strShCoreFooter = "";

$strShcoreCSSUpdate = "";

// このbrush:があれば、シンタックスハイライトする必要があるページ。上部と下部に必要なCSSやJSを足しこむ
if ( strpos($strPageTemplate, "brush:") != false ) {
    $strShCoreHeader = '<link rel="stylesheet" type="text/css" href="./hilight/styles/shcore-3.0.83.min.css?v=%(shcorecssupdate)s">' ;

    // shcoreのスタイルシート
    $timeShcoreCSSUpdate = filemtime("./hilight/styles/shcore-3.0.83.min.css");
    $strShcoreCSSUpdate = date("YmdHis", $timeShcoreCSSUpdate);

    $strShCoreFooter = "<script type='text/javascript' src='./hilight/scripts/shcore-3.0.97.min.js'></script>\n" .
                       "<script type='text/javascript'>\n" .
                       "SyntaxHighlighter.defaults['toolbar'] = false;\n" .
                       "SyntaxHighlighter.defaults['auto-links'] = false;\n" .
                       "SyntaxHighlighter.all();\n" .
                       "</script>\n";
}
//-------------- シンタックスハイライター系ここまで --------------


// "%(hilight)s"があれば置き換え
// ソースハイライト用。"%(hilight)s"がある時だけ埋め込まれる
$strHilightJS = "";
$strPageTemplate = str_replace("%(hilight)s", $strHilightJS, $strPageTemplate);

// シンタックスハイライター


// MATHJAX
$strMathJaxCoreFooter = "";

// 必要であれば、maxjaxを機能させる。
if ( strpos($strPageTemplate, "(mathjax)s") != false ) {
    $strMathJaxCoreFooter = <<<MATHJAX
<script type="text/x-mathjax-config">
MathJax.Hub.Config({
  tex2jax: {
    inlineMath: [['$','$']],
    displayMath: [['$$','$$']],
    processEscapes: true,
    processClass: "asciimath2jax_process",
    ignoreClass: "sitebody"
  },
  CommonHTML: { matchFontHeight: false }
});
</script>
<script type="text/javascript" async
  src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.2/MathJax.js?config=TeX-AMS_CHTML">
</script>
MATHJAX;

$strMathJaxJS = "";
$strPageTemplate = str_replace("%(mathjax)s", $strMathJaxJS, $strPageTemplate);

}

// MATHJAX



// ファイルのアーカイブがあれば、更新日時へと置き換え
$fileArchieve = "";
if (isset($filetime_hash[$urlParamPage])) {
    $fileArchieve = $filetime_hash[$urlParamPage];
}
if ($fileArchieve) {
    $filetime = filemtime($fileArchieve);
    $fileKeys   = array( "%(file)s", "%(year)04d", "%(mon)02d", "%(mday)02d" );
    $fileValues = array($fileArchieve, date("Y", $filetime), date("m", $filetime), date("d", $filetime) );

    $strPageTemplate = str_replace($fileKeys, $fileValues, $strPageTemplate);
}


// css系を置き換え
$strStyleTemplate = file_get_contents("style_dynamic.css");

$normalizeUrlParamPage = $urlParamPage; // 普通のページならそのまま?page=○○○ の部分へと置き換える

// 今表示しているページへの太字等
$strStyleTemplate = str_replace("%(menu_style_dynamic)s", $normalizeUrlParamPage, $strStyleTemplate);

// トップページのテンプレートの読み込み
$strIndexTemplate = file_get_contents("index_RPD.html");


//--------------------------------------------------------
// 以下、indexファイルから、「前のページ」と「後のページ」を作成する
function strip_html_comment($str)
{
    $str = preg_replace("/<\!--.*?-->/sm", '', $str); //comments
    return $str;
}

$explode_text = explode( "\n", strip_html_comment( $strIndexTemplate ) );

$explode_text_line_count = count( $explode_text );
// 格納用の配列
$explode_array = array();
for( $i=0; $i<$explode_text_line_count; $i++ ) {
    preg_match("/id=\"(nobu_.+?)\"/", $explode_text[$i], $explode_line_match);
    if ($explode_line_match) {
        if ( array_key_exists( $explode_line_match[1], $content_hash ) ) {
            if ( count($explode_array) > 0 ) {
                // すでに要素があるなら、付けたし
                if ($explode_line_match[1] != $explode_array[0]) {
                    $content_hash[ $explode_line_match[1] ]["prev"] = $explode_array[0];
                }
            }

            array_unshift( $explode_array, $explode_line_match[1]);
        }
    }
}

$current_page_prev = "";
$current_page_next = "";

$array_content_style = array();
$array_content_template = array();

foreach ($content_hash as $content_key => $content_value) {
    array_push($array_content_style, "%(" . $content_key . ")s");
    // 今のページと同じで、"prev" がある場合、
    if ($content_key == $urlParamPage && array_key_exists( "prev", $content_value ) ) {
        $current_page_prev = $content_value["prev"];

    // 今表示しているページとは異なるキーが、prevとして今のページを指定しているということは、
    // そのキーは、現在表示しているページのnextである。 
    } else if ( array_key_exists( "prev", $content_value ) && $content_value["prev"] == $urlParamPage) {
        $current_page_next = $content_key;
    }
}


if ($current_page_prev != "") {
    $current_page_prev = '<li class="page-item"><a class="page-link" href="?page=' .$current_page_prev. '"><i class="fa fa-caret-left fa-fw"></i>前へ</a></li>';
}

if ($current_page_next != "") {
    $current_page_next = '<li class="page-item"><a class="page-link" href="?page=' .$current_page_next. '">次へ<i class="fa fa-caret-right fa-fw"></i></a></li>';
}

if ($current_page_prev != "" || $current_page_next != "") {
    $footer_control_page = "\n" . '<div class="content-box mb-3 content-lighten"><ul class="pagination justify-content-center" style="margin:0px">%(prev)s %(next)s</ul></div>' . "\n";
    $strPageTemplate = $footer_control_page . $strPageTemplate . $footer_control_page;
}


// ページ内の上下に「前へ」と「次へ」を付け加える。
$array_style    = array( 
    "%(prev)s",
    "%(next)s" );
$array_template = array( 
    $current_page_prev, 
    $current_page_next );
$strPageTemplate = str_replace($array_style, $array_template, $strPageTemplate);
// 以上、作成でした
//--------------------------------------------------------



// 左のメニューの部分。すでに開いているページに基いて、階層を開くところを決める。
// javascriptの一部を書き出す感じ
$strMenuExpand = "";
if ($orgParamPage != "" and $content_hash[$urlParamPage]['dir'] != "#") {
    $strMenuExpand = "$( '#menu' ).multilevelpushmenu( 'expand', '" . $content_hash[$urlParamPage]['dir'] . "' )";
}

// メインのスタイルシート
$timeStyleUpdate = filemtime("./style.min.css");
$strStyleUpdate = date("YmdHis", $timeStyleUpdate);

// 独自のFontAwesome
$timeFontPluginUpdate = filemtime("./font-awesome/css/font-awesome-plugin.css");
$strFontPluginUpdate = date("YmdHis", $timeFontPluginUpdate);

// メニューのカスタムCSS
$timeMLPMCSSUpdate = filemtime("./jquery/jquery.multilevelpushmenu.min.css");
$strMLPMCSSUpdate = date("YmdHis", $timeMLPMCSSUpdate);

// メニューのカスタムJS
$timeMLPMCustomUpdate = filemtime("./jquery/jquery.multilevelpushmenu.custom.min.js");
$strMLPMCustomUpdate = date("YmdHis", $timeMLPMCustomUpdate);

// index内にある、スタイル、コンテンツ、階層の開きをそれぞれ、具体的な文字列へと置き換える
$array_style    = array(
    "%(style_dynamic)s",
    "%(expand)s",
    "%(styleupdate)s",
    "%(fontpluginupdate)s",
    "%(mlpmcssdate)s",
    "%(mlpmcustomdate)s",
    "%(shcore_head)s",
    "%(shcore_foot)s",
    "%(shcorecssupdate)s",
    "%(mathjax_foot)s",
    "%(content_dynamic)s"
);
$array_template = array(
    $strStyleTemplate,
    $strMenuExpand,
    $strStyleUpdate,
    $strFontPluginUpdate,
    $strMLPMCSSUpdate,
    $strMLPMCustomUpdate,
    $strShCoreHeader,
    $strShCoreFooter,
    $strShcoreCSSUpdate,
    $strMathJaxCoreFooter,
    $strPageTemplate
);
$strIndexEvaluated = str_replace($array_style, $array_template, $strIndexTemplate);

// トップページとして表示
echo($strIndexEvaluated);
?>

