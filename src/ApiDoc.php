<?php

/**
 * Class ApiDoc
 */
class ApiDoc {

    /**
     * 模板文件扩展名
     */
    const TEMPLATE_FILE_EXTENSION = '.html';

    /**
     * 注释文件扩展名
     */
    const DOC_FILE_EXTENSION = '.php';

    /**
     * 需要生成文档的文件路径
     *
     * @var string
     */
    protected $documentPath;

    /**
     * 保存生成文档的路径
     *
     * @var string
     */
    protected $savePath;

    /**
     * 项目名称
     *
     * @var string
     */
    protected $name = 'example';

    /**
     * 是否开启控制器名转换
     *
     * @var bool
     */
    protected $openControllerChange = true;

    /**
     * 控制器名中出现大写字母次数时转换
     *
     * @var int
     */
    protected $controllerFrequency = 1;

    /**
     * 是否开启方法名转换
     *
     * @var bool
     */
    protected $openMethodChange = true;

    /**
     * 方法名中出现大写字母次数时转换
     *
     * @var int
     */
    protected $methodFrequency = 2;

    /**
     * 模板文件名
     *
     * @var string
     */
    protected $templateName = 'template';

    /**
     * ApiDoc constructor.
     *
     * @param $documentPath
     * @param null $savePath
     */
    public function __construct($documentPath, $savePath = null) {

        $this->setDocumentPath($documentPath);

        if (is_null($savePath)) {

            $this->setSavePath(getcwd() . DIRECTORY_SEPARATOR);

        } else {

            $this->setSavePath($savePath);

        }
    }

    /**
     * 设置camelCase to snake_case的配置
     *
     * @param bool $openController 是否开启控制器转换
     * @param bool $openMethod 是否开启方法名转换
     * @param int $controllerFrequency 控制器名中出现大写字母次数时转换
     * @param int $methodFrequency 方法名中出现大写字母次数时转换
     * @return void
     */
    public function setCamel2SnakeConfig($openController = true, $openMethod = true, $controllerFrequency = 1, $methodFrequency = 2): void {
        $this->openControllerChange = $openController;
        $this->openMethodChange = $openMethod;
        $this->controllerFrequency = $controllerFrequency;
        $this->methodFrequency = $methodFrequency;
    }

    /**
     * camelCase to snake_case
     *
     * @param string $str 字符串
     * @param integer $upperTimes 出现几次大写字母才转换,默认1次
     * @return string
     */
    private function camelCase2SnakeCase($str, $upperTimes = 1) {

        if (preg_match_all('/[A-Z]/', $str) >= $upperTimes) {

            $str = preg_replace_callback('/([A-Z]{1})/', function ($matches) {
                return '_' . strtolower($matches[0]);
            }, $str);

            if ('_' === $str[0]) {
                $str = substr_replace($str, '', 0, 1);
            }

            return $str;
        }

        return $str;
    }

    /**
     * 执行
     *
     * @param bool $fetch 是否直接输出
     * @return bool|mixed|string
     */
    public function init($fetch = false) {

        $fileList = [];

        $this->getAllFileInPath($this->getDocumentPath(), $fileList);

        $tableData = '';
        $sidebar = []; // 侧边栏列表

        foreach ($fileList as $fileName) {

            $fileData = file_get_contents($fileName);
            $data = $this->getAllNeedTransDoc($fileData);

            foreach ($data as $oneDoc) {
                $infoData = $this->parse($oneDoc, $fileName);
                $sidebar[basename($fileName)][] = [
                    'methodName' => $infoData['methodName'],
                    'requestUrl' => $infoData['requestUrl'],
                ];
                $tableData .= $this->generateTable($infoData);
            }
        }

        $templateFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->getTemplateName() . self::TEMPLATE_FILE_EXTENSION;
        $templateData = file_get_contents($templateFile);
        $result = str_replace('{name}', $this->getName(), $templateData);
        $result = str_replace('{main}', $tableData, $result);
        $result = str_replace('{right}', $this->generateSidebar($sidebar), $result);
        $result = str_replace('{date}', date('Y-m-d H:i:s'), $result);

        if (!$fetch) {

            file_put_contents($this->getSaveFilePathAndName(), $result);
            exit();

        } else {

            return $result;
        }
    }

    /**
     * 获取文件夹下的所有PHP(可设置)文件
     *
     * @param string $path 路径
     * @param array $fileList 结果保存的变量
     * @param bool $all 可选,true全部,false当前路径下,默认true.
     */
    private function getAllFileInPath($path, &$fileList = [], $all = true) {

        if (!is_dir($path)) {

            $fileList = [];

            return;
        }
        $data = scandir($path);

        foreach ($data as $oneFile) {

            if ('.' === $oneFile || '..' === $oneFile) {
                continue;
            }

            $thisFilePath = $path . DIRECTORY_SEPARATOR . $oneFile;

            $isDir = is_dir($thisFilePath);

            $extension = $this->getFileExtension($oneFile);

            if (!$isDir && $extension === self::DOC_FILE_EXTENSION) {

                $fileList[] = $thisFilePath;

            } elseif ($isDir && $all) {

                $this->getAllFileInPath($thisFilePath, $fileList, $all);

            }
        }
    }

    /**
     * 获取文件扩展名
     *
     * @param string $filename 完整文件名
     * @return string
     */
    protected function getFileExtension($filename) {
        return strrchr($filename, '.');
    }

    /**
     * 获取所有可以生成文档的注释
     *
     * @param string $data 代码文件内容
     * @return array
     */
    private function getAllNeedTransDoc($data) {

        preg_match_all(
            '/(\/\*\*.*?\*\sapi.*?\*\/\s*(public|private|protected)?\s*function\s+.*?\s*?\()/s',
            $data,
            $matches
        );

        return $matches[1] ?? [];
    }

    /**
     * 解析允许的每一条注释
     *
     * @param string $data 注释文本
     * @param string $fileName 文件名
     * @return array
     */
    private function parse($data, $fileName) {
        $fileName = basename($fileName);
        $return = [];

        preg_match_all(
            '/(public|private|protected)?\s*function\s+(.*?)\(/',
            $data,
            $matches
        );
        $return['funcName'] = $matches[2][0] ?: '[null]';

        preg_match_all(
            '/\/\*\*\s+\*\s+(.*?)\s+\*\s+api\s+/s',
            $data,
            $matches
        );
        $return['methodName'] = $matches[1][0] ?: '[null]';

        preg_match_all(
            '/\s+\*\s+api\s+(.*?)\s+(.*?)\s+(\s+\*\s+@)?.*/',
            $data,
            $matches
        );
        $return['requestName'] = $matches[1][0] ?: '[null]';
        $return['requestUrl'] = $matches[2][0] ?: '[null]';

        if ($this->openControllerChange) {
            $return['requestUrl'] = str_replace(
                '{controller}',
                $this->camelCase2SnakeCase($fileName, $this->controllerFrequency),
                $return['requestUrl']
            );
        }
        if ($this->openMethodChange) {
            $return['requestUrl'] = str_replace(
                '{method}',
                $this->camelCase2SnakeCase($return['funcName'], $this->methodFrequency),
                $return['requestUrl']
            );
        }

        preg_match_all(
            '/\s+\*\s+@param\s+(.*?)\s+(.*?)\s+(.*?)\s/',
            $data,
            $matches
        );

        $return['param'] = [];

        if (!empty($matches[1])) {

            for ($i = 0; $i < count($matches[1]); $i++) {

                $return['param'][] = [
                    'type' => $matches[1][$i] ?: '[null]',
                    'var' => $matches[2][$i] ?: '[null]',
                    'description' => $matches[3][$i] ?: '[null]'
                ];
            }

        }

        preg_match_all(
            '/\s+\*\s+@return\s+(.*?)\s+(.*?)\s+(.*?)\s/',
            $data,
            $matches
        );

        $return['return'] = [];

        if (!empty($matches[1])) {

            for ($i = 0; $i < count($matches[1]); $i++) {
                $type = $matches[1][$i] ?: '[null]';
                $var = $matches[2][$i] ?: '[null]';
                $description = $matches[3][$i] ?: '[null]';
                if (false !== strpos($description, '*/')) {
                    $description = $var;
                    $var = '';
                }
                $return['return'][] = [
                    'type' => $type,
                    'var' => $var,
                    'description' => $description,
                ];
            }

        }

        return $return;
    }

    /**
     * 每个API生成表格
     *
     * @param array $data 解析后的API信息
     * @return string
     */
    private function generateTable($data) {

        $tableDataString = $this->getHeader($data);

        if (count($data['param']) > 0) {

            $tableDataString .= $this->getTableHeader('请求');

            foreach ($data['param'] as $tr) {

                $tableDataString .= $this->getTableContent($tr);
            }
            $tableDataString .= $this->getTableBottom();
        }

        if (count($data['return']) > 0) {

            $tableDataString .= $this->getTableHeader('返回');

            foreach ($data['return'] as $tr) {

                $tableDataString .= $this->getTableContent($tr);
            }
            $tableDataString .= $this->getTableBottom();
        }

        $tableDataString .= $this->getBottom();

        return $tableDataString;
    }

    /**
     * 获取头部
     *
     * @param array $data
     * @return string
     */
    protected function getHeader($data) {
        return '<div id="' . base64_encode($data['requestUrl']) . '" class="api-main">
        <div class="title">' . $data['methodName'] . '</div>
        <div class="body">
            <table class="layui-table">
                <thead>
                    <tr>
                        <th>
                        ' . $data['requestName'] . '
                        </th>
                        <th rowspan="3">
                        ' . $data['requestUrl'] . '
                        </th>
                    </tr>
                </thead>
            </table>
        </div>';
    }

    /**
     * 获取表格头部
     *
     * @param string $prefix
     * @return string
     */
    protected function getTableHeader($prefix) {
        return '<div class="body">
                <table class="layui-table">
                    <thead>
                        <tr>
                            <th>' . $prefix . '名称</th>
                            <th>' . $prefix . '类型</th>
                            <th>' . $prefix . '说明</th>
                        </tr>
                    </thead>
                    <tbody>';
    }

    /**
     * 获取表格内容
     *
     * @param array $tr
     * @return string
     */
    protected function getTableContent($tr) {
        return "<tr>
                    <td>{$tr['var']}</td>
                    <td>{$tr['type']}</td>
                    <td>{$tr['description']}</td>
                </tr>";
    }

    /**
     * 获取表格底部
     *
     * @return string
     */
    protected function getTableBottom() {
        return '</tbody></table></div>';
    }

    /**
     * 获取底部
     *
     * @return string
     */
    protected function getBottom() {
        return '<hr></div>';
    }

    /**
     * 生成侧边栏
     *
     * @param array $originalData 侧边列表数组
     * @return string html代码
     */
    private function generateSidebar($originalData) {
        $sidebarString = '';
        foreach ($originalData as $key => $blockquote) {

            $sidebarString .= '<blockquote class="layui-elem-quote layui-quote-nm right-item-title">' . $key . '</blockquote>
            <ul class="right-item">';

            foreach ($blockquote as $li) {

                $sidebarString .= '<li><a href="#' . base64_encode($li['requestUrl']) . '">';
                $sidebarString .= "<cite>{$li['methodName']}</cite>";
                $sidebarString .= "<em>{$li['requestUrl']}</em></a></li>";

            }
            $sidebarString .= '</ul>';
        }

        return $sidebarString;
    }

    /**
     * @return string
     */
    protected function getDocumentPath(): string {
        return $this->documentPath;
    }

    /**
     * @param string $documentPath
     */
    protected function setDocumentPath($documentPath): void {
        $this->documentPath = $documentPath;
    }

    /**
     * @return string
     */
    protected function getSavePath(): string {
        return $this->savePath;
    }

    /**
     * @param string $savePath
     */
    protected function setSavePath($savePath): void {
        $this->savePath = $savePath;
    }

    /**
     * @return string
     */
    protected function getName(): string {
        return $this->name;
    }

    /**
     *
     * @param string $name
     */
    public function setName($name): void {
        $this->name = $name;
    }

    /**
     * @return string
     */
    protected function getTemplateName(): string {
        return $this->templateName;
    }

    /**
     * @param string $templateName
     */
    public function setTemplateName(string $templateName): void {
        $this->templateName = $templateName;
    }

    /**
     * 获取生成的API文档保存路径和文件名
     *
     * @return string
     */
    protected function getSaveFilePathAndName() {
        return $this->getSavePath() . $this->getName() . self::TEMPLATE_FILE_EXTENSION;
    }
}
