# grunt构建说明

## 安装Node.js

- [Node.js](http://nodejs.org/)
- 最新版本的nodejs自带NPM(包管理工具)

## 前提条件

- 确保本地已经获取到最新的`Gruntfile.js`和`package.json`

## 构建准备

- 打开`Node.js command prompt`

- cd 到`linksFrontEnd`目录

- **首次使用**需要通过NPM安装grunt的插件
```
E:\links123\linksFrontEnd> npm install
```
- 安装完成后`linksFrontEnd`目录会多出`node_modules`文件夹，该文件夹无需提交到git

## 开始构建

- 输入grunt会自动构建，但为了防止错误操作，默认键入`grunt`不做任何操作，请输入构建目标，当前主站前端项目的构建目标为`build`

```
E:\links123\linksFrontEnd> grunt build
```

- 出现`Done，without errors`表示构建成功

- `linksFrontEnd`目录下的`dest`文件的内容应该会被重新生成，如果你还不确定，请清空'dest'目录，再次运行`grunt build`，构建结束后`dest`目录会生成文件夹以及文件