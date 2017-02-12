#README.md
##说明
冒烟测试自动化脚本,入口文件main.php
##用例编写说明
1. 支持excel编写用例表,每个worksheet表示一个待测试接口,worksheet名字为待测试接口名
2. 用例使用utf-8编码
3. worksheet必须包含3列,依次为'name' => 'A', 'param' => 'B', 'assert' => 'C',其余列随意编写,不影响测试
4. asset列格式如下:
  - 需要用param的值做变量替换的地方,使用$前缀修饰,如搜索词替换$search
  - 结果json节点引用需用大括号括起来,如{$search.data.rows}
  - 目前支持的函数:
    - php原生函数均支持:count,empty,preg_match等
    - 自定义函数(用于数组所有内容判断):equal($arr, $key, $value),greater($arr, $key, $value),lesser($arr, $key, $value)