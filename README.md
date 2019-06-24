# Draggable
移动端 元素自由拖拽/悬浮/自动停靠/依附插件/jquery插件
https://github.com/1131122353/Draggable/edit/master/README.md

使用
2.导入draggable.js、jquery-2.1.1.min.js

```
<script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
```

```
<script src="./js/draggable.js"></script>
```

```
<script type="text/javascript">
	var f = function(){
		console.log('拖拽中')
	}
	var t = function(){
		console.log('释放拖拽')
	}
	$('#draggable').draggable({attachment:true,touchmoveCall:f,touchendCall:t})
	$('#draggable1').draggable({attachment:true,touchmoveCall:f,touchendCall:t})
</script>
```

 移动端 demo地址: https://pj.ngrok.xiaomiqiu.cn

