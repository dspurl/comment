import Network from '../utils/network.js'
export default {
	detail(id,success,fail){
		Network.setGetMessage('comment/detail/' + id,{},'加载中', function (res) {
		  success(res)
		}, function (res) {
		  uni.showToast({
			title: res.message,
			icon: 'none',
			duration: 2000
		  })
		})
	},
	good(data,success,fail) {
		Network.setGetMessage('comment/good',data,'加载中', function (res) {
		  success(res)
		}, function (res) {
		  uni.showToast({
			title: res.message,
			icon: 'none',
			duration: 2000
		  })
		})
	},
	create(id,data,success,fail) {
		Network.setPostMessage('comment/' + id,data,'处理中', function (res) {
		  success(res)
		}, function (res) {
		  uni.showToast({
			title: res.message,
			icon: 'none',
			duration: 2000
		  })
		})
	}
};
