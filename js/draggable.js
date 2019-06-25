;(function($){
	var draObj = function(ele,opt = {}){
    		this.$element = ele
    		this.default = {
    			attachment:true, //是否开启自动停靠依附 默认 true
    			position:{},     //设置拖拽释放后停靠位置, 默认空,就近依附，参数形式 {x（int）, y (int)}.
    			animate:true,    //是否开启过渡动画，默认 true
    			_animatetime: '3', //过渡动画时间, 默认'3'
    			touchmoveCall:function(){}, //拖拽过程中钩子回调函数（可选）
    			touchendCall:function(){} //拖拽释放后钩子回调函数（可选）
    		}
    		this.options = $.extend({}, this.default, opt)
    }
	draObj.prototype = {
    	_start:function(){
    		var _this = this
    		this.$element.on('touchmove',function(e){
				e.preventDefault();//阻止'默认行为'
			    // 鼠标相对于当前页面的坐标
			   	var pageX = e.originalEvent.targetTouches[0].pageX
			   	var pageY = e.originalEvent.targetTouches[0].pageY
			   	// 获取窗口的宽度和高度
			 	var w = $(window).width()
			 	var h = $(window).height()
			   	// 限定元素移动的范围
			    min_x = - $(this).width()/2
			    min_y = - $(this).height()/2
			    max_x = w - $(this).width()/2
			    max_y = h - $(this).height()/2
			    // 计算元素移动后的位置
			    x = pageX - $(this).height()/2
			    y = pageY - $(this).height()/2
			    if (x > max_x) {
			    	x = max_x
			    }
			    if (x < min_x) {
			    	x = min_x
			    }
			    if (y > max_y) {
			    	y = max_y
			    }
			    if (y < min_y) {
			    	y = min_y
			    }
			    $(this).offset({
				      top: y,
				      left: x
				});
				return _this.options.touchmoveCall()
			})
			this.$element.on('touchend',function(e){
				e.preventDefault();//阻止'默认行为'
				if (!_this.options.attachment) {
					_this.options.touchendCall()
					return false
				}
				// 获取窗口的宽度和高度
			 	var w = $(window).width()
			 	var h = $(window).height()
			 	// console.log(w)
			 	min_x = - $(this).width()/2
			    min_y = - $(this).height()/2
			    max_x = w - $(this).width()/2
			    max_y = h - $(this).height()/2
			    // 获取元素当前位置
			   	var x1 = $(this).offset()
			   	// 计算元素和窗口最短距离的方向
			   	var d = new Array()
			   	d['left']   = w - (w - x1.left)
			   	d['right']  = w - x1.left
			   	d['top']    = h - (h - x1.top)
			   	d['bottom'] = h - x1.top
				
				var f = d['left'] < d['right'] ? 'left' : 'right'
				var t = d['top']  < d['bottom'] ? 'top' : 'bottom' 
				var z = d[f] < d[t] ? f : t

			 	if (z == 'left') {
			 		y = x1.top
			 		x = min_x
			 	}
			 	if (z == 'right') {
			 		y = x1.top
			 		x = max_x

			 	}
			 	if (z == 'top') {
			 		x = x.left
			 		y = min_y
			 	}
			 	if(z == 'bottom'){
			 		x = x.left,
			 		y = max_y
			 	}
			 	if (!_this.options.animate) {return _this.options.touchendCall()}
			 	var s =  _this.s(x, y)
			 	$(this).animate(s,_this.options._animatetime)
				_this.options.touchendCall()
			})
			
    	},
    	s:function(x, y){
    		if (this.options.position.x !== undefined && this.this.options.position.y !== undefined) {
    			styleObj = {'top':this.this.options.position.y,'left':this.this.options.position.x}
    		}else{
    			styleObj = {'top':y,'left':x}
    		}
    		return styleObj
		}
   
	}

	$.fn.draggable = function(options = {}){
    	var obj = new draObj(this,options)
    	return obj._start()
    }
})(jQuery)
