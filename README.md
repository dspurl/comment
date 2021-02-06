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

###### 增加评价通知代码

```php
#api\app\Notifications\Common.php
/**
  * 订单评价提醒
  * @param $parameter //传入的参数
  * @return array
*/
public function orderEvaluate($parameter)
{
    $return = [
        'result' => 'ok',
        'msg' => '成功'
    ];
    $parameter = collect($parameter);
    $verification = $this->verification($parameter, ['id', 'identification', 'confirm_time', 'template', 'user_id']);
    if ($verification['result'] == 'error') {
        return $verification;
    }
    $invoice = [
        'type' => InvoicePaid::NOTIFICATION_TYPE_SYSTEM_MESSAGES,
        'title' => '待评价提醒',
        'list' => [
            [
                'keyword' => '订单编号',
                'data' => $parameter['identification']
            ],
            [
                'keyword' => '完成时间',
                'data' => $parameter['confirm_time']
            ]
        ],
        'remark' => '点击详情,您可以对订单进行评价',
        'url' => '/pages/order/score?id=' . $parameter['id'],
        'parameter' => $parameter,
        'prefers' => ['database', 'wechat', 'mail']
    ];
    $user = User::find($parameter['user_id']);
    $user->notify(new InvoicePaid($invoice));
    return $return;
}
/**
  * 用户评价通知
  * @param $parameter    //传入的参数
  * @return array
*/
public function adminOrderEvaluate($parameter){
    $return = [
        'result'=>'ok',
        'msg'=>'成功'
    ];
    $parameter = collect($parameter);
    $verification=$this->verification($parameter,['id','details','time','cellphone','template']);
    if($verification['result'] == 'error'){
        return $verification;
    }
    $invoice=[
        'type'=> InvoicePaid::NOTIFICATION_TYPE_SYSTEM_MESSAGES,
        'title'=>'收到新的评价消息',
        'list'=>[
            [
                'keyword'=>'评价人',
                'data'=>$parameter['cellphone']
            ],
            [
                'keyword'=>'评价内容',
                'data'=>$parameter['details']
            ],
            [
                'keyword'=>'评价时间',
                'data'=>$parameter['time']
            ]
        ],
        'remark'=>'点击查看详细信息',
        'url'=>'/tool/comment/commentList?model_id='.$parameter['id'],
        'parameter'=>$parameter,
        'admin'=>true,
        'prefers'=>['wechat','mail']
    ];
    if(config('notification.account')){
        $account=explode(',',config('notification.account'));
        foreach ($account as $uid) {
            $user = User::find($uid);
            $invoice['parameter']['user_id']=$uid;
            $user->notify(new InvoicePaid($invoice)); // 发送通知
        }
    }
    return $return;
}
```



###### 增加评价状态

- 默认用户收货后，订单就已经结束，需要评价需要添加评价环节，示例代码=

```php
#api\app\Http\Controllers\v1\Client\GoodIndentController.php
public function receipt($id)
{
    // $GoodIndent->state = GoodIndent::GOOD_INDENT_STATE_ACCOMPLISH;
    $GoodIndent->state = GoodIndent::GOOD_INDENT_STATE_EVALUATE;
    // return array(1, '收货成功');
    $Common = (new Common)->orderEvaluate([
        'id' => $GoodIndent->id,  //订单ID
        'identification' => $GoodIndent->identification,  //订单号
        'confirm_time' => $GoodIndent->confirm_time,    //确认收货时间
        'template' => 'order_evaluate',   //通知模板标识
        'user_id' => $GoodIndent->User->id    //用户ID
    ]);
    if ($AdminCommon['result'] == 'ok') {
        return array(1, '收货成功');
    } else {
        return array($Common['msg'], Code::CODE_PARAMETER_WRONG);
    }
}
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
		//关闭订单后重新加载
        refreshOderList(){
            this.navList= [
                ...
                {
                     state: 4,
                     text: '待评价',
                    loadingType: 'more',
                    orderList: []
                }
            ]
            this.page = 1
            this.loadData()
        },
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
		//关闭订单后重新加载
        refreshOderList(){
            this.navList= [
                ...
                {
                     state: 4,
                     text: '待评价',
                    loadingType: 'more',
                    orderList: []
                }
            ]
            this.page = 1
            this.loadData()
        },
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

###### 添加商品评价记录

```php
#app\Models\v1\GoodIndentCommodity.php
/**
  * 获取评价
  */
public function Comment(){
    return $this->morphOne('App\Models\v1\Comment', 'model');
}
```



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



## 如何更新插件
- 将最新版的插件下载，并替换老的插件，后台可一键升级
## 如何卸载插件
- 后台可一键卸载
