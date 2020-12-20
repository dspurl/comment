#评价
## 说明
- 该插件依赖dsshop项目，而非通用插件
- 支持版本:dshop v1.1.8及以上
- 已同步版本：dsshop v1.4

## 功能介绍
- 支持对商品进行评价，平台可通过后台进行回复
- 支持为同一订单多件商品进行评价
- 商品详情页显示评价记录，默认显示2条，在评价列表显示全部，每次加载8条数据
- 支持用户匿名评价
- 支持用户上传图片、填写评价内容、进行星级评分，默认只要进行星级评分和评价内容即可完成评价
- 评价列表显示用户购买的商品规格，展示评价上传的图片，并支持图片预览
- 数据表为灵活设计，即可以支持其它评价，只需要设置`model_type`和`model_id `即可

## 使用说明
#### 一、 下载comment最新版
#### 二、 解压comment到项目plugin目录下
#### 三、 登录dshop后台，进入插件列表
#### 四、 在线安装（请保持dshop的目录结构，如已部署到线上，请在本地测试环境安装，因涉及admin和uni-app，不建议在线安装）
#### 五、 进入api目录执行数据库迁移使用

```
php artisan migrate
```
#### 六、 进入数据库，导入 `comment/comment.sql` SQL文件（sql文件需要按照下图进行修改，也可后台自行添加权限，效果一样）
![](/image/1.png)
![](/image/2.png)
#### 七、 进入后台，为管理员分配权限
#### 八、 使用评价插件，如这是第一个插件，可直接按以下步骤替换目标文件即可，如安装有其它插件，可能存在修改同一文件的可能，请进行文件比对进行手动修改
- `comment/example/admin/list.vue`->`admin/src/views/Indent/list.vue`
- `comment/example/api/Element/GoodIndentController.php`->`api/app/Http/Controllers/v1/Element/GoodIndentController.php`
- `comment/example/api/Models/GoodIndentCommodity.php`->`api/app/Http/Controllers/v1/Models/GoodIndentCommodity.php`
- `comment/example/api/trade/order/order.vue`->`trade/Dsshop/pages/order/order.vue`
- `comment/example/api/trade/product/product.vue`->`trade/Dsshop/pages/product/product.vue`
- `comment/example/api/trade/user/user.vue`->`trade/Dsshop/pages/user/user.vue`
#### 九、 测试评价、后台对评价进行回复、商品详情页是否显示对应的评价内容，如果功能都能正常使用，则说明你的插件安装成功
## 如何更新插件
- 首先请备份项目，升级可能产生问题（如自行修改了涉及到升级的文件、下载的文件不全等问题）
- 首先查看新版本支持的dshop的版本，如果符合，可通过后台直接升级，升级将会自动覆盖原有文件
- 如果升级涉及到手动修改代码部分，升级说明中会进行讲解
## 如何卸载插件
- 插件安装后不建议卸载，因为涉及到多处手动修改的代码
- 可以按以上安装方式反向操作，即可卸载
