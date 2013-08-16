# 另客网--英语迷，桌面控 #

- 禅道项目管理工具地址:alm.links123.cn

- 测试网站地址：test.links123.net

- 提交代码后查看一下返回信息，是否自动build通过，如果通过到测试地址查看自己提交上去的内容是否正确，如果正确的话在gitlab里面发起一个合并代码的请求。

## 默认分支 ##
我们项目的默认分支是`develop`大家`clone`项目的时候一定要`clone`默认分支
`git clone -b develop git@git.links123.cn:Jim/links123.git`
## 对于php自动构建工具Phing的使用 ##

**Phing**是一个基于Apache ANT 的项目构建系统，Phing可以做传统构建系统比如 GNU make 能做的任何事情，同时没有陡峭的学习曲线。利用Phing结合其他php工具何以轻松实现代码规范检查，代码覆盖率检查，代码自动发布等任务。

### 使用方法 ###

代码提交后会自动执行我们`build.xml`里面定义的`deploy`任务，如果要执行额外的任务可以在提交日志里面添加`{任务名}`如`{lint}`

### 忽略项目中的config.php ###

大家在clone完项目以后运行如下命令本地git库即可忽略对config.php的版本记录:

`git update-index --assume-unchanged filename`

使本地git库重新跟踪config.php运行下面的命令:

`git update-index --no-assume-unchanged filename`

注：filename包含config.php文件的路径