<?php


class Index {

    /**
     * 组合一句问候
     * api GET /index/hello
     * @param string $name 你想问候的人
     * @param string $say 问候语
     * @return string 组合后的话
     */
    public function hello(string $name, string $say): string {
        return "Hello,{$name},{$say}";
    }
}
