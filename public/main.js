$(function(){
	var tH = $(window).height();
	var logoH = tH-10;
	$("#leftlogo").css("height",logoH+"px");
	$("#leftlogo").css("line-height",logoH+"px");
	$("#search").css("height",0.3*tH+"px");
	$("#subit").css("height",0.3*tH+"px");
	$(".option").css("top",0.1*tH+"px");
	$("#search").css("top",0.3*tH+"px");
	$("#subit").css("top",0.3*tH+2+"px");
	
	$(window).resize(function(){
		var tH = $(window).height();
		var logoH = tH-10;
		$("#leftlogo").css("height",logoH+"px");
		$("#leftlogo").css("line-height",logoH+"px");
		$("#search").css("height",0.3*tH+"px");
		$("#subit").css("height",0.3*tH+"px");
		$(".option").css("top",0.1*tH+"px");
		$("#search").css("top",0.3*tH+"px");
		$("#subit").css("top",0.3*tH+2+"px");
	});
	
	$(".tabs ul li").click(function(){
		$(this).addClass('current').siblings().removeClass('current');
		var index=$(this).index();
		$('.tabs').siblings().addClass('hide');
		$('.tabs').siblings().eq(index).removeClass('hide');
	});
	$('p').click(function(){
		$(this).next().slideToggle(200);
		$(this).siblings('p').next().hide();
	});
	$('.content li').click(function(){
		$('.content li').removeClass('acurrent');
		$(this).addClass('acurrent');
	});
});