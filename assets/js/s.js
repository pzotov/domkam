/*
var proizv__video = null;

var iframe = $("#proizv-info__video");
/!*
Включаем кеширование по-умолчанию, для getScript
 *!/
$.ajaxSetup({cache: true});
if(iframe.length) {
	//Загружаем Youtube API только на тех страницах, где оно нужно
	$.getScript("https://www.youtube.com/iframe_api");
}
/!**
 * Функция вызывается после загрузки Youtube API
 *!/
function onYouTubePlayerAPIReady() {
	proizv__video = new YT.Player('proizv-info__video', {});
}
*/

$(function(){
	var $menu = $(".menu"),
		$menu__toggler = $(".menu__toggler"),
		menu_shown = false,
		$menu_spans = $(".menu__item-a span"),
		rewided = false,
		$links = $(".links"),
		$wrapper = $(".wrapper"),
		$menu__others = $(".menu__others");
	/**
	 * В зависимости от размера экрана переносим меню дополнительных ссылок
	 * в блок с основным меню или в основную страницу, меняя при этом класс
	 * для разного отображения
	 */
	$(window).resize(function(){
		var ww = $(window).width();
		if(ww>768 || menu_shown) {
			$menu.show();
			$menu_spans
				.css("width", "auto")
				.each(function(){
					$(this).width($(this).parent().width());
				});
			rewided = true;
		} else {
			$menu.hide();
		}
		if(ww>768){
			$links
				.removeClass("links_menu")
				.addClass("links_float")
				.appendTo($wrapper);
		} else {
			$links
				.addClass("links_menu")
				.removeClass("links_float")
				.appendTo($menu__others);
		}
		if(ww<=480){
			$(".cats__items-wrap").each(function(){
				$(".cats__items", this).addClass("swiper-wrapper");
				$(".cats__item", this)
					.addClass("swiper-slide")
					.off("click")
				;
				$(this).swiper({
					loop: true,
//					centeredSlides: true,
					slidesPerView: 'auto',
					loopedSlides: $(".swiper-slide", this).length,
				});
			});
			//В мобильной версии параметры камня должны находиться после аналогов и объектов
			$(".stone__params-box").appendTo(".stone__info .container");
		} else {
			$(".cats__items-wrap").each(function(){
				var swiper = $(this).data("swiper"),
					cat_info = $(this).siblings(".cat-info").find(".cat-info__box"),
					cat_info_title = cat_info.find(".cat-info__title"),
					cat_info_img = cat_info.find(".cat-info__image img"),
					cat_info_benefits = cat_info.find(".cat-info__benefits");
				if(swiper) swiper.destroy(true, true);
				$(".cats__items", this).removeClass("swiper-wrapper");
				$(".cats__item", this).on("click", function(e){
					e.preventDefault();
					cat_info.hide();
					cat_info_title.html($(this).find(".cats__title").html());
					cat_info_img.attr("src", $(this).find(".cats__image img").attr("src"));
					cat_info_benefits.html($(this).data("benefits"));
					cat_info.show();
				}).first().trigger("click");
			});

			//На десктопах и планшетах параметры камня должны быть справа от картинки
			$(".stone__params-box").insertAfter(".stone__image-box");
		}
	}).trigger("resize");

	/**
	 * Показать/скрыть меню по кнопке в мобильной версии
	 */
	$menu__toggler.click(function(e){
		e.preventDefault();
		if(menu_shown) {
			$menu.slideUp();
			$(this).removeClass("menu__toggler_open");
		} else {
			$menu.slideDown();
			$(this).addClass("menu__toggler_open");
			if(!rewided){
				$menu_spans
					.css("width", "auto")
					.each(function(){
						$(this).width($(this).parent().width());
					});
				rewided = true;
			}
		}
		menu_shown = !menu_shown;
	});

	/**
	 * Выпадающее меню, по клику и ховеру
	 */
	$(".links2").each(function(){
		var li = $(this).parent();
		li.mouseenter(function(){
				$(".links2", this).first().show();
				$(this).addClass("links__item_open");
			})
			.mouseleave(function(){
				$(".links2", this).first().hide();
				$(this).removeClass("links__item_open");
			})
			.find(".links__item-a").first()
			.click(function(e){
				e.preventDefault();
				$(".links2", li).first().toggle();
				li.toggleClass("links__item_open");
			});
	});

	/**
	 * Смена телефона в зависимости от выбранного города
	 */
	$(".header__phone-select select").change(function(){
		var v = $(this).val();
		$(".header__phone-box").html(v);
		$(".header__phone-select select").val(v);
	});

	/**
	 * Слайдер
	 */
	initSwiper();

	/**
	 * Наше производство
	 */
	$(".proizv").each(function(){
		var proizv = this,
			w = $(".proizv__wrapper",this).width(),
			h = $(".proizv__wrapper",this).height(),
			$markers = $(".proizv__marker", this),
			$info = $(".proizv-info", this),
			$bg = $(".proizv__wrapper img",this),
			wi = $info.outerWidth(),
			hi = $info.outerHeight(),
			$info_title = $(".proizv-info__title", this),
			$info_descr = $(".proizv-info__descr", this),
			$info_image = $(".proizv-info__image img", this),
			$info_video = $(".proizv-info__video iframe", this)
			;

		$markers.click(function(e){
			e.preventDefault();
			if($(this).hasClass("proizv__marker_active")){
				$bg.trigger("click");
				return;
			}
			var time = $(this).data("time");
			$markers.removeClass("proizv__marker_active");
			$(this).addClass("proizv__marker_active");
			if($info.is(":visible")) $info.hide();
			$info_title.html($(this).attr("title"));
			$info_descr.html($(this).data("descr"));
			$info_image.attr("src", $(this).data("image"));
			$info_video.attr("src", $(this).data("video"));
			var l = $(this).position().left,
				t = $(this).position().top;

			if(t < 20) t = 20;
			else if(t+hi>=h-20){
				if(t-hi+16 > 20) t = t-hi+16;
				else t = Math.round((h-hi)/2);
			}
			if(l < 20) l = 20;
			if(l+wi > w-20) {
				if(l-wi >= 20) l = l-wi;
				else l = Math.round((w-wi)/2);
			}

			$info.css({
				left: l,
				top: t
			}).slideDown();

			//if(proizv__video){
			//	proizv__video.seekTo(time);
			//	proizv__video.playVideo();
			//}
		});

		$bg.click(function(){
			$info.hide();
			$markers.removeClass("proizv__marker_active");

			//if(proizv__video){
			//	proizv__video.pauseVideo();
			//}

			$info_video.attr("src", "about:blank");
		}).load(function(){
			w = $(".proizv__wrapper",proizv).width();
			h = $(".proizv__wrapper",proizv).height();
		});

	});

	/**
	 * Список камней в каталоге
	 */
	$(".stones_catalog").each(function(){
		var blocks = $(".stones__blocks", this),
			form = $(".stones__form", this);

		$("#s_sort, #s_color, #s_group").change(function(){
			$.fancybox.showLoading();
			$.get(form.attr("action"), form.serialize()+'&isNaked=1', function(res){
				blocks.html(res);
				initSwiper(".stones__blocks");
				$.fancybox.hideLoading();
			});
		});
	});

	/**
	 * Аналоги и объекты в карточке камня
	 */
	/*
	$(".related").each(function() {
		$(".related__slider",this).swiper({
			slidesPerView: 'auto',
			loop: true,
			prevButton: $(".related__arrow_prev", this)[0],
			nextButton: $(".related__arrow_next", this)[0]
		});
	});
	*/
	$(".related_analogs .related__item").click(function(e){
		e.preventDefault();
		$(".stone__image .related__item").remove();
		$(this).clone().appendTo(".stone__image");
	});

	/**
	 *
	 */
	$(".project__order-button, .application__title").click(function(e){
		var zayavka = $(".zayavka");
		if(zayavka.length){
			e.preventDefault();
			var comment = $(this).data('comment');
			if(!comment) comment = 'Интересует: ' + $(this).html();
			$("[name=f_Text]", zayavka).val(comment);
			$("html, body").animate({
				scrollTop: zayavka.offset().top
			}, 500);
		}
	});

	/**
	 * Во всех полях, где нужно вводить телефон, даем пользователю вводить только цифры
	 */
	$(".phone-input").mask("+7 (999) 999-99-99");

	/**
	 * Все формы на сайте показываются без поля posting, добавляем его скриптом,
	 * так будет меньше спама
	 */
	$(".form").append('<input type="hidden" name="posting" value="1">');

	/**
	 * Стилизация поля [type=file]
	 */
	$(".form__file").each(function(){
		var input = this,
			value = null;

		$(this).wrap('<div class="file clearfix" />');
		var wrapper = $(this).parent()
		value = $('<div class="file__value form__input" />').appendTo(wrapper);
		$('<div class="file__button form__button">Выбрать</div>').appendTo(wrapper);
		$(this).wrap('<div class="file__file" />');

		$(input).change(function(){
			var val = $(this).val().split(/\\/im);
			value.html(val[val.length-1]);
		});
	});

	/**
	 * Общий вызов fancybox
	 * После показа форм внутри fancybox нужно добавить к ним posting,
	 * а также настроить поля для ввода телефона
	 */
	$(".fancybox").fancybox({
		padding: 2,
		afterLoad: function(){
			if(this.type=='ajax') this.content = this.content.replace(/\?isNaked=1/,'');
		},
		afterShow: function(){
			$(".fancybox-inner .phone-input").mask("+7 (999) 999-99-99");
			$(".fancybox-inner .form").append('<input type="hidden" name="posting" value="1">');
		},
		helpers: {
			overlay: {
				locked: false
			}
		}
	});
});

function initSwiper(prefix){
	var prefix = prefix || "";
	$(prefix + " .swiper").each(function() {
		var p = $(this).parent();
		if($(".swiper-slide", this).length<2) {
			p.find(".swiper-arrow").hide();
			return;
		}
		var options = {
			autoplay: $(this).data("autoplay"),
			slidesPerView: 'auto',
			centeredSlides: $(this).data("centered"),
			//loopedSlides: $(".swiper-slide", this).length,
			loop: !$(this).data('noloop'),
			pagination: p.find('.swiper-bullets'),
			paginationClickable: true
		};
		if($(this).data("arrows")){
			options.prevButton = p.find(".swiper-arrow--prev");
			options.nextButton = p.find(".swiper-arrow--next");
		}
		$(this).swiper(options);
	});
}
