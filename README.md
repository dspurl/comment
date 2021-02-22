#评价
## 说明
- 该插件依赖dsshop项目，而非通用插件
- 支持版本:dsshop v2.0.0及以上
- 已同步版本：dsshop v2.0.0

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
#### 三、 登录dsshop后台，进入插件列表
#### 四、 在线安装（请保持dsshop的目录结构，如已部署到线上，请在本地测试环境安装，因涉及admin和uni-app，不建议在线安装）
#### 五、 进入api目录执行数据库迁移使用

```
php artisan migrate
```
#### 六、进入后台，添加权限

| **权限名称** | **API**        | **分组**   | **菜单图标** | **显示在菜单栏** |
| ------------ | -------------- | ---------- | ------------ | ---------------- |
| 评价         | Comment        | 工具       | 否           | 是               |
| 评价列表     | CommentList    | 工具->评价 | 否           | 是               |
| 评价回复     | CommentCreate  | 工具->评价 | 否           | 否               |
| 评价操作     | CommentEdit    | 工具->评价 | 否           | 否               |
| 评价删除     | Commentdestroy | 工具->评价 | 否           | 否               |

#### 七、 进入后台，为管理员分配权限
#### 八、 使用说明

##### 管理员南

- 用户评价后，管理员可以通过评价管理对用户的评价内容进行审核，审核通过的才会在前台展示
- 管理员可以把用户评价的内容进行删除，删除后该条评价和回复都会被删除

##### 开发指南

###### 增加评价统计代码

```php
#api\app\Http\Controllers\v1\Client\GoodIndentController.php
public function quantity()
{
    ...
    'waitforreceiving' => 0, //待收货
    'remainEvaluated' => 0, //待评价
    ...
    } else if ($indent->state == GoodIndent::GOOD_INDENT_STATE_TAKE) {
    	$return['waitforreceiving'] += 1;
	} else if ($indent->state == GoodIndent::GOOD_INDENT_STATE_EVALUATE) {
    	$return['remainEvaluated'] += 1;
	}
}
```

```vue
#trade\Dsshop\pages\user\user.vue
<template>
</template>
<script>
export default {
    data() {
		return {
			quantity: {
                all: 0,
                obligation: 0,
                waitdeliver: 0,
                waitforreceiving: 0
            },
		};
	},
    onShow(){
        if(this.hasLogin){
            this.getUser()
            this.browse()
            this.noticeConut()
            this.getQuantity()
            this.getUserCouponCount()
        } else {
            this.browseList = []
            this.user = {}
            this.noticeNumber = null
            this.quantity = {
                all: 0,
                obligation: 0,
                waitdeliver: 0,
                waitforreceiving: 0
            }
        }

    },
}
</script>
```



###### 添加待评价和评价按钮

```vue
#trade\Dsshop\pages\user\user.vue
<template>
	<!-- 订单 -->
	<view class="order-section">
        ...
        <view class="order-item" @click="navTo('/pages/order/order?state=4')" hover-class="common-hover"  :hover-stay-time="50">
            <text class="yticon icon-yishouhuo"><text v-if="quantity.remainEvaluated" class="cu-tag badge">{{quantity.remainEvaluated}}</text></text>
            <text>待评价</text>
        </view>
    </view>
</template>
<script>

</script>
```

```vue
#trade\Dsshop\pages\order\order.vue
<template>
	<view class="action-box b-t">
        ...
        <block v-if="item.state === 4">
        	<button class="action-btn recom" @tap="goScore(item)">立即评价</button>
        </block>
    </view>
</template>
<script>
export default {
    data() {
		return {
			navList: [
                ...
            	{
                    state: 4,
                    text: '待评价',
                    loadingType: 'more',
                    orderList: []
            	}
            ],
		};
	},
    methods: {
        // 评价
        goScore(item){
            uni.navigateTo({
                url: `/pages/order/score?id=${item.id}`
            })
        },
        //评价成功后回调
        refreshOderList(){
            // 需要重新加载
            this.loadData()
        }
    }
}
</script>
```

```vue
#trade\Dsshop\pages\order\showOrder.vue
<template>
	<!-- 底部 -->
	<view v-if="indentList.state === 1 || indentList.state === 3 || indentList.state === 4" class="footer">
        <view class="price-content"></view>
        <navigator v-if="indentList.state === 1" :url="'/pages/money/pay?id=' + indentList.id" hover-class="none" class="submit">立即支付</navigator>
        <view v-else-if="indentList.state === 3" class="submit" @click="confirmReceipt(indentList)">确认收货</view>
        <view v-else-if="indentList.state === 4" class="submit" @click="goScore(indentList)">立即评价</view>
    </view>
</template>
<script>
export default {
    methods: {
        // 评价
        goScore(item){
            uni.navigateTo({
                url: `/pages/order/score?id=${item.id}`
            })
        },
        //评价成功后回调
        refreshOderList(){
            // 需要重新加载
            this.getList()
        }
    }
}
</script>
```
###### 添加后台订单进度对评价的支持

```vue
#admin\src\views\IndentManagement\Indent\components\Detail.vue
<template>
</template>
<script>
export default {
    data() {
		return {
		};
	},
    methods: {
		getList() {
            case 5:
            case 10:
              this.order_progress = 4
              break
        }
    }
}
</script>
```
###### 添加订单评价相关状态

```php
#\app\Models\v1\GoodIndent.php
const GOOD_INDENT_STATE_EVALUATE = 10; //状态：待评价
const GOOD_INDENT_STATE_HAVE_EVALUATION = 11; //状态：已评价
public function getStateShowAttribute()
{
   ...
	case static::GOOD_INDENT_STATE_REFUND_FAILURE:
    	$return = '退款失败';
    	break;
    case static::GOOD_INDENT_STATE_EVALUATE:
        $return = '待评价';
        break;
    case static::GOOD_INDENT_STATE_HAVE_EVALUATION:
    	$return = '已评价';
    	break;
}
```
###### 添加商品评价关联

```php
#app\Models\v1\GoodIndentCommodity.php
/**
  * 获取评价
  */
public function Comment(){
    return $this->morphOne('App\Models\v1\Comment', 'model');
}
```

###### 添加商品评价记录

```vue
#trade\Dsshop\pages\product\product.vue
<template>
		<!-- 评价 -->
		<view class="eva-section">
			<view class="e-header">
				<text class="tit">评价</text>
				<text>({{commentTotal}})</text>
				<navigator hover-class="none" class="tip" :url="'comment?id='+ id">查看全部</navigator>
				<text class="yticon icon-you"></text>
			</view>
			<view class="eva-box" v-for="(item,index) in commentList" :key="index">
				<image class="portrait" :src="item.comment.portrait || '/static/missing-face.png'"  mode="aspectFill" lazy-load></image>
				<view class="right">
					<text class="name">{{item.comment.name}}</text>
					<text class="con">{{item.comment.details}}</text>
					<view class="bot">
						<text class="attr">购买类型：<span v-for="(ite,ind) in item.good_sku.product_sku" :key="ind" class="padding-right-xs">{{ite.value}}</span></text>
						<text class="time">{{item.comment.created_at.split(' ')[0]}}</text>
					</view>
				</view>
			</view>
		</view>
</template>
<script>
import Comment from '../../api/comment'
export default {
    data() {
		return {
			...
			commentList: [],
			commentTotal:0
		};
	},
    async onLoad(options) {
		if (id) {
			...
			this.goodEvaluate()
		}
	},
    methods: {
		// 获取评价列表
		goodEvaluate(){
			const that = this
			Comment.good({
				limit: 2,
				page: 1,
				good_id:that.id,
				sort:'-created_at'
			},function(res){
				that.commentList = res.data
				that.commentTotal = res.total
			})
		}
    }
}
</script>
```

配置模板通知

```php
#api\config\notification.php
'wechat'=>[ //微信公众号
    ...
    'order_evaluate'=>env('WECHAT_SUBSCRIPTION_INFORMATION_ORDER_EVALUATE',''),  //订单评价提醒
    'admin_order_evaluate'=>env('WECHAT_SUBSCRIPTION_INFORMATION_ADMIN_ORDER_EVALUATE',''),  //用户评价通知
    ],
```

```shell
#.env
WECHAT_SUBSCRIPTION_INFORMATION_ORDER_EVALUATE=
WECHAT_SUBSCRIPTION_INFORMATION_ADMIN_ORDER_EVALUATE=
```



## 如何更新插件
- 将最新版的插件下载，并替换老的插件，后台可一键升级
## 如何卸载插件
- 后台可一键卸载
