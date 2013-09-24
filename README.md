# 另客网--英语迷，桌面控 #

- 禅道项目管理工具地址:alm.links123.cn

- 测试网站地址：test.links123.net

- 提交代码后查看一下返回信息，是否自动build通过，如果通过到测试地址查看自己提交上去的内容是否正确，如果正确的话在gitlab里面发起一个合并代码的请求。

### 默认分支 ###
我们项目的默认分支是`develop`大家`clone`项目的时候一定要`clone`默认分支
`git clone -b develop git@git.links123.cn:Jim/links123.git`

### 开发环境 ###

#### shoaly绿色服务端 : ####
- (`下载地址http://pan.baidu.com/share/link?shareid=604559635&uk=2519257532`)
- 双击根目录下面 /server/usbwebserver.exe
- 打开浏览器 地址栏输入 <http://localhost:82>  , 希望成功 :) 

为避免本地环境和服务器环境不一致造成本地正常但服务器端不正常，统一使用一致的开发环境

1. 安装virtualbox
2. 安装vagrant(`http://www.vagrantup.com/`)
3. 进入要初始化vagrant的目录，可以是任意目录
4. `vagrant init lnmp filepath.box`(用迅雷等下载工具下载完以后，指定到本地路径)
5. 编辑Vagrantfile中guest机（虚拟机）与主机的端口映射和共享目录
`config.vm.network :forwarded_port, guest: 80, host: 80`
`config.vm.synced_folder "project_dir", "/mnt/www/links123"`
6. `vagrant up`
7. 可以使用putty登入虚拟机进行操作(IP:127.0.0.1 PORT:2222 USER:vagrant PASSWD:vagrant)

备注：centos镜像文件放在百度云盘，我会定期维护（Paul） `http://pan.baidu.com/share/link?shareid=3153135892&uk=2753792316`

PS 如果本地环境电脑比较老，cpu不支持64位虚拟化技术的话，就不能使用Paul提供的正统的开发环境了，
可以采用如下方法搭建：
方法一 全linux开发环境
1、使用32位的虚拟机镜像文件（lamp）
vagrant box add  lamp http://www.hitarthindia.com/VM/lampp-mongo-git-lithium-rock.box
vagrant init lamp
vagrant up
2、自己动手部署ngix
 configure 安装nginx依赖库pcre
 configure 安装nginx
 配置nginx.conf
 启动nginx
方法二 使用WAMP环境
使用windows下的amp环境
1、下载wamp安装文件 http://www.wampserver.com/en/
2、安装建立windows下的php、mysql运行环境
3、适配nginx的conf文件 与apache的配置文件

### 对于php自动构建工具Phing的使用 ###

**Phing**是一个基于Apache ANT 的项目构建系统，Phing可以做传统构建系统比如 GNU make 能做的任何事情，同时没有陡峭的学习曲线。利用Phing结合其他php工具何以轻松实现代码规范检查，代码覆盖率检查，代码自动发布等任务。

### 使用方法 ###

代码提交后会自动执行我们`build.xml`里面定义的`deploy`任务，如果要执行额外的任务可以在提交日志里面添加`{任务名}`如`{lint}`

### 忽略项目中的config.php ###

大家在clone完项目以后运行如下命令本地git库即可忽略对config.php的版本记录:

`git update-index --assume-unchanged filename`

使本地git库重新跟踪config.php运行下面的命令:

`git update-index --no-assume-unchanged filename`

注：filename包含config.php文件的路径

### 远程测试数据库连接 ###

需要在搭建本地测试环境的成员，数据库可以连接测试数据库

`Host: 112.124.15.96 DB: links123_public USER: links123_public PASSWORD: links1230820`
