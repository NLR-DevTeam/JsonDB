<!--
    ----------------------------------------------------------------
    ArkPowered Studio
    方块盒子工作室
    ----------------------------------------------------------------
    程序版本；Release v1.0.0
    年度序列：22w01a
    JSON_DB数据库函数集文件，JSON数据库程序
    Github仓库：https://github.com/CarlSkyCoding/JsonDB
    作者：SkyGod
    ----------------------------------------------------------------
!-->
<link rel="stylesheet" href="//cdn.arkpowered.cn/css/page/offical/jsonDB.css">
<?php


//  ----------------------------------------------------------------
//                          Setting 设置
//  ----------------------------------------------------------------
$GLOBALS["setting_path_to_Tablelist"] = true;
// 决定了如果旧版本的path.json与新版本的Tablelist.json冲突，数据库重名的话双方的优先等级
// true为Tablelist.json , false为path.json
$GLOBALS["setting_autoCheckUpdate"] = false;
// 是否自动检查更新并且告知您，如果为true，在有更新时，将会立即把您传送到提示的窗口，建议不启用，可以检查更新用

//常量设置，请不要更改此内容
$GLOBALS["Version"] = 6;
$GLOBALS["versionRead"] = "Release v1.0.0";
$GLOBALS["AnnualSerial"] = "22w01a";

$rootPath = $_SERVER["DOCUMENT_ROOT"];
$nowPath = getcwd() . "/";
$GLOBALS["rootPath"] = $_SERVER["DOCUMENT_ROOT"];
$GLOBALS["nowPath"] = getcwd() . "/";

$GLOBALS["checkingUpdateURL"] = "https://api.arkpowered.cn/update/check/jsonDB.json";
$GLOBALS["listTablePath"] = getcwd() . "/jsonDB_store/Tablelist.json";
$GLOBALS["logTablePath"] = getcwd() . "/jsonDB_store/log.out";
$GLOBALS["SettingPath"] = getcwd() . "/jsonDB_store/Setting.json";

//运行前检查与设置
if (chmod($nowPath, 0755)) {
    if (!is_dir("{$nowPath}jsonDB_store")) {
        mkdir("{$nowPath}jsonDB_store");
    }
    if (!file_exists($GLOBALS["listTablePath"])) {
        if (fopen($GLOBALS["listTablePath"], "w+") == false) {
            echo "<br><a class='notice-head'>[jsonDB基础系统]</a> 创建数据库表文件(TablelistJSON,fopen [w+])权限不足，请您提高根目录的权限";
            exit();
        }
    }
    if (!file_exists("{$nowPath}jsonDB_store/log.out")) {
        if (fopen($logTablePath, "w+") == false) {
            echo "<br><a class='notice-head'>[jsonDB基础系统]</a> 创建日志文件(Log,fopen [w+])权限不足，请您提高根目录的权限";
            exit();
        }
    }
    if (!file_exists("{$nowPath}jsonDB_store/setting.json")) {
        if (fopen($GLOBALS["SettingPath"], "w+") == false) {
            echo "<br><a class='notice-head'>[jsonDB基础系统]</a> 创建设置文件(SettingJSON,fopen [w+])权限不足，请您提高根目录的权限";
            exit();
        }
    }
} else {
    echo "<br><a class='notice-head'>[jsonDB基础系统]</a> 创建文件夹权限不足，请您提高根目录的权限";
    exit();
}

//检查path.json
if (file_exists("{$nowPath}jsonDB_store/path.json")) {
    $data = file_get_contents($GLOBALS["listTablePath"]);
    $data = json_decode($data, true);
    $oldData = file_get_contents("{$GLOBALS['nowPath']}jsonDB_store/path.json");
    $oldData = json_decode($oldData, true);
    foreach ($oldData as $key => $value) {
        if (isset($data[$key])) {
            if ($GLOBALS["setting_path_to_Tablelist"] == true) {
            } else {
                $data[$key] = $value;
            }
        } else {
            $transingArray = array(
                $key => $value,
            );
            $data = array_merge($data, $transingArray);
        }
    }
    file_put_contents($GLOBALS["listTablePath"], json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    unlink("{$nowPath}jsonDB_store/path.json");
}

//检查更新部分
if ($GLOBALS["setting_autoCheckUpdate"] == false) {
} else {
    jsonDB_checkupdate();
}

function jsonDB_checkupdate()
{
    $response = file_get_contents($GLOBALS["checkingUpdateURL"]);
    $response = json_decode($response, true);
    if ($response["latestVersion"] > $GLOBALS["Version"]) {
        header("location: //arkpowered.cn/notice.php?reason=您的 JsonDB 不是最新版本。您可以前往 https://github.com/CarlSkyCoding/JsonDB 进行更新&targettitle=Github仓库&target=//github.com/CarlSkyCoding/JsonDB");
    }
}

//主要 JsonDB 程序部分
function jsonDB_create($table, $content, $description)
{
    if (is_array($content) && isset($description)) {
        $data = file_get_contents($GLOBALS["listTablePath"]);
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
            file_put_contents($GLOBALS["listTablePath"], json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
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
    $data = file_get_contents($GLOBALS["listTablePath"]);
    $data = json_decode($data, true);
    if (isset($data[$table])) {
        unset($data[$table]);
        file_put_contents($GLOBALS["listTablePath"], json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
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
    $data = file_get_contents($GLOBALS["listTablePath"]);
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
    $data = file_get_contents($GLOBALS["listTablePath"]);
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
    $data = file_get_contents($GLOBALS["listTablePath"]);
    $data = json_decode($data, true);
    if (isset($data[$table])) {
        if (is_array($content)) {
            $data[$table]["table_content"] = $content;
            file_put_contents($GLOBALS["listTablePath"], json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
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
