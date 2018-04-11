$(".tooltip-mouse").mouseenter(function(){
    $(".tooltip-bottom", this).addClass("tooltip-hover-bt");
    $(".tooltip-top", this).addClass("tooltip-hover-tp");
    $(".tooltip-left", this).addClass("tooltip-hover-lf");
    $(".tooltip-right", this).addClass("tooltip-hover-rg");
});
$(".tooltip-mouse").mouseleave(function(){
    $(".tooltip-bottom").removeClass("tooltip-hover-bt");
    $(".tooltip-top").removeClass("tooltip-hover-tp");
    $(".tooltip-left").removeClass("tooltip-hover-lf");
    $(".tooltip-right").removeClass("tooltip-hover-rg");
});