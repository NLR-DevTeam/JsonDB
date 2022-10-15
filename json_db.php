<!--
    ----------------------------------------------------------------
    ArkPowered Studio
    方块盒子工作室
    ----------------------------------------------------------------
    程序版本；Alpha-Codeing-0.5 Beta
    JSON_DB数据库函数集文件，JSON数据库程序
    Github仓库：https://github.com/CarlSkyCoding/JsonDB
    作者：SkyGod
    ----------------------------------------------------------------
!-->
<link rel="stylesheet" href="//cdn.arkpowered.cn/css/page/offical/jsonDB.css">
<?php
//Setting 设置（如果不懂或者作者没有让你改，请不要动）

$version = 5;
$versionRead = "Alpha-Codeing-0.5 Beta";

$rootPath = $_SERVER["DOCUMENT_ROOT"];
$nowPath = getcwd() . "/";
$GLOBALS["rootPath"] = $_SERVER["DOCUMENT_ROOT"];
$GLOBALS["nowPath"] = getcwd() . "/";

//常量设置，请不要更改此内容
$updateCheckurl = "//api.arkpowered.cn/update/check/jsonDB.json";
$listTablePath = getcwd() . "/jsonDB_store/path.json";
$logTablePath = getcwd() . "/jsonDB_store/log.out";

//运行前检查与设置
if (chmod($nowPath, 0755)) {
    if (!is_dir("{$nowPath}jsonDB_store")) {
        mkdir("{$nowPath}jsonDB_store");
    }
    if (!file_exists("{$nowPath}jsonDB_store/path.json")) {
        if (fopen($listTablePath, "w+") == false) {
            echo "<br><a class='notice-head'>[jsonDB基础系统]</a> 创建数据库表文件(SettingJSON,fopen [w+])权限不足，请您提高根目录的权限";
            exit();
        }
    }
    if (!file_exists("{$nowPath}jsonDB_store/log.out")) {
        if (fopen($logTablePath, "w+") == false) {
            echo "<br><a class='notice-head'>[jsonDB基础系统]</a> 创建日志文件(Log,fopen [w+])权限不足，请您提高根目录的权限";
            exit();
        }
    }
} else {
    echo "<br><a class='notice-head'>[jsonDB基础系统]</a> 创建文件夹权限不足，请您提高根目录的权限";
    exit();
}
//主要函数部分

function jsonDB_create($table, $content, $description)
{
    if (is_array($content) && isset($description)) {
        $data = file_get_contents("{$GLOBALS['nowPath']}jsonDB_store/path.json");
        $data = json_decode($data, true);
        if (!isset($data[$table])) {
            $new_table = array(
                $table => array(
                    "table_content" => $content,
                    "table_describe" => $description,
                    "createTime" => time()
                )
            );
            $data = array_merge($data, $new_table);
            file_put_contents("{$GLOBALS['nowPath']}jsonDB_store/path.json", json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $table_output = array(
                "code" => 100,
                "operationType" => "create",
                "result" => "成功创建此数据库"
            );
        } else {
            $table_output = array(
                "code" => 106,
                "operationType" => "create",
                "error_message" => "表已存在"
            );
        }
    } else {
        $table_output = array(
            "code" => 103,
            "operationType" => "create",
            "error_message" => "{$table}表的Content内容并非Array数组，请输入一个Array数组。或者是您没有填写Description，请在函数第三项填写此值"
        );
    }
    return $table_output;
}

function jsonDB_delete($table)
{
    $data = file_get_contents("{$GLOBALS['nowPath']}jsonDB_store/path.json");
    $data = json_decode($data, true);
    if (isset($data[$table])) {
        unset($data[$table]);
        file_put_contents("{$GLOBALS['nowPath']}jsonDB_store/path.json", json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $table_output = array(
            "code" => 100,
            "operationType" => "delete",
            "result" => "成功删除此数据库"
        );
    } else {
        $table_output = array(
            "code" => 101,
            "operationType" => "cover",
            "error_message" => "无法找到{$table}表，请核对后重试"
        );
    }
    return json_encode($table_output);
}

function jsonDB_connect($table)
{
    $data = file_get_contents("{$GLOBALS['nowPath']}jsonDB_store/path.json");
    $data = json_decode($data, true);
    if (isset($data[$table])) {
        $table_content = $data[$table]["table_content"];
        $table_output = array(
            "code" => 100,
            "operationType" => "connect",
            "table_content" => $table_content
        );
    } else {
        $table_output = array(
            "code" => 101,
            "operationType" => "connect",
            "error_message" => "无法找到{$table}表，请核对后重试"
        );
    }
    return json_encode($table_output);
}

function jsonDB_connect_INSIDE($table, $path)
{
    $data = file_get_contents("{$GLOBALS['nowPath']}jsonDB_store/path.json");
    $data = json_decode($data, true);
    if (isset($data[$table])) {
        if (substr($path, 0, 1) !== "/" && substr($path, -1, 1) !== "/") {
            $table_content = $data[$table]["table_content"];
            $explode_array = explode("/", $path);
            $count = count($explode_array);
            $discount = 0;
            $nextArray = $table_content;
            while ($count > 0) {
                if (isset($nextArray[$explode_array[$discount]])) {
                    $nextArray = $nextArray[$explode_array[$discount]];
                    $count = $count - 1;
                    $discount = $discount + 1;
                    $table_output = array(
                        "code" => 100,
                        "operationType" => "connect_INSIDE",
                        "result" => $nextArray
                    );
                } else {
                    $count = -1;
                    $table_output = array(
                        "code" => 105,
                        "operationType" => "connect_INSIDE",
                        "error_message" => "输入错误，某一表集不存在，请检查输入正确或表集存在"
                    );
                }
            }
        } else {
            $table_output = array(
                "code" => 104,
                "operationType" => "connect_INSIDE",
                "error_message" => "输入错误，结尾和开头不应该存在斜线"
            );
        }
    } else {
        $table_output = array(
            "code" => 101,
            "operationType" => "connect_INSIDE",
            "error_message" => "无法找到{$table}表，请核对后重试"
        );
    }
    return json_encode($table_output);
}

function jsonDB_cover($table, $content)
{
    $data = file_get_contents("{$GLOBALS['nowPath']}jsonDB_store/path.json");
    $data = json_decode($data, true);
    if (isset($data[$table])) {
        if (is_array($content)) {
            $data[$table]["table_content"] = $content;
            file_put_contents("{$GLOBALS['nowPath']}jsonDB_store/path.json", json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $table_output = array(
                "code" => 100,
                "operationType" => "cover",
                "result" => "成功覆盖此数据库"
            );
        } else {
            $table_output = array(
                "code" => 102,
                "operationType" => "cover",
                "error_message" => "{$table}表无法被修改，因为新的Json内容并非Json格式或变量类型"
            );
        }
    } else {
        $table_output = array(
            "code" => 101,
            "operationType" => "cover",
            "error_message" => "无法找到{$table}表，请核对后重试"
        );
    }
    return json_encode($table_output);
}

function jsonDB_Tools_merge($array_A, $array_B)
{
    if (is_array($array_A) && is_array($array_B)) {
        $outArray = array_merge($array_A, $array_B);
        $tools_output = array(
            "code" => 200,
            "operationType" => "Tools_merge",
            "result" => $outArray
        );
    } else {
        $tools_output = array(
            "code" => 201,
            "operationType" => "Tools_merge",
            "error_message" => "变量不是数组"
        );
    }
}
