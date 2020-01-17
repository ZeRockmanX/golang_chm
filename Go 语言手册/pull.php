<?php

function getFileData($file)
{
    if (!is_file($file)) {
        exit('没有文件');
    }
    $handle = fopen($file, 'r');
    if (!$handle) {
        exit('读取文件失败');
    }
    $line = 0;
    $arrayData = array();
    while (($data = fgetcsv($handle)) !== false) {
        $data[4]  = mb_convert_encoding($data[2], "UTF-8");
        // 下面这行代码可以解决中文字符乱码问题
        $data[2]  = mb_convert_encoding($data[2], "GBK");
        $line++;
        // 跳过第一行标题
        if ($line == 1) {
            continue;
        }
        $arrayData[] = $data;
    }
    fclose($handle);
    return $arrayData;
}
$baseTop = '<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="renderer" content="webkit" />
<meta name="force-rendering" content="webkit"/>
<meta name="applicable-device" content="pc,mobile" />
<meta name="MobileOptimized" content="width" />
<meta name="HandheldFriendly" content="true" />
<meta http-equiv="Cache-Control" content="no-transform" />
<meta http-equiv="Cache-Control" content="no-siteapp" />
<meta name="format-detection" content="telephone=no" />
<link href="../base/common.css?v=1.5.0" rel="stylesheet" />
<title>';

$baseBottom = <<<eof
</div></div></div>
<script src="../base/jquery1.12.4.min.js"></script>
<script src="../base/common.js?v=1.5.0"></script>
</body>
</html>
eof;

$br = <<<eof


eof;

$file_path = __DIR__ . "\\html\\";
$csv = __DIR__ . "\\" . "url.csv";
$arrayData = getFileData($csv);
$catalog = "";

$repData = array();
$i = 0;
foreach ($arrayData as $key => $info) {
    $url = $info[1];
    $name = $info[0] . " " . str_replace(array("\r\n", "\r", "\n"), "", $info[4]) . ".html";
    $url = str_replace('http://c.biancheng.net/view/', '/view/', $url);
    $repData[$i][0] = $url;
    $repData[$i][1] = $name;
    $i++;
}

foreach ($arrayData as $key => $info) {
    $file_url = $info[1];
    $file_name_1 = $info[0];
    $file_name_2 =  str_replace(array("\r\n", "\r", "\n"), "", $info[2]) . ".html";
    $file_name_3 =  str_replace(array("\r\n", "\r", "\n"), "", $info[4]) . ".html";
    $txt = file_get_contents($file_url);
    preg_match('/<div id="arc-body">[\s\S]*<div class="pre-next-page clearfix">/i', $txt, $matches);
    $info = preg_replace('/<div class="pre-next-page clearfix">/', "", $matches[0]);
    $info = preg_replace("/<a href='\//", "<a href='../", $info);
    $info = preg_replace("/ src=\"\//", ' src="../', $info);
    $info = str_replace('<pre class="go">', '<pre class="go"><blockquote style="font-weight: bold;">', $info);
    $info = str_replace('</pre>', '</blockquote></pre>', $info);
    $save_info = $baseTop . '</title></head><body><div id="main" class="clearfix"><div id="article-wrap"><div id="article">' . $info . $baseBottom;
    $myfile = fopen($file_path . $file_name_1 . " " . $file_name_2, "w") or die("Unable to open file!");
    $save_info = mb_convert_encoding($save_info, "UTF-8");

    foreach ($repData as $repKey => $repInfo) {
        $save_info = str_replace('href="' . $repData[$repKey][0], 'href="' . $repData[$repKey][1], $save_info);
    }

    fputs($myfile, $save_info);
    fclose($myfile);
    $catalog .= '<a href="./' . $file_name_1 . ' ' . $file_name_3 . '">' . $file_name_1 . ' ' . $file_name_3 . '</a><br/>' . $br;
}
$catalogData = $baseTop . '</title></head><body>' . $catalog . '</body></html>';
$catalogData = mb_convert_encoding($catalogData, "UTF-8");
$indexCatalog = fopen($file_path . '00 index.html', "w") or die("Unable to open file!");
fputs($indexCatalog, $catalogData);
fclose($indexCatalog);
