var youtube_iframes = $(".advantages__iframe");

/*
Включаем кеширование по-умолчанию, для getScript
 */
$.ajaxSetup({cache: true});
if(youtube_iframes.length) {
	//Загружаем Youtube API только на тех страницах, где оно нужно
	$.getScript("https://www.youtube.com/iframe_api");
}
/**
 * Функция вызывается после загрузки Youtube API
 */
function onYouTubePlayerAPIReady() {
	youtube_iframes.each(function(){
		var player = new YT.Player($(this).attr("id"), {});
		$(this).data("player", player);
	});
}

$(function(){
	var $menu = $(".menu"),
		$menu__toggler = $(".menu__toggler"),
		menu_shown = false,
		$menu_spans = $(".menu__item-a span"),
		rewided = false,
		$links = $(".links"),
		$wrapper = $(".wrapper"),
		$menu__others = $(".menu__others"),
		$info = $(".links2_info");

	if($info.length){
		$info.insertAfter(".links__item-a_info");
	}

	$(window).resize(function(){
		var ww = $(window).width();
		/**
		 * В зависимости от размера экрана переносим меню дополнительных ссылок
		 * в блок с основным меню или в основную страницу, меняя при этом класс
		 * для разного отображения
		 */
		if(ww>768){
			$links
				.removeClass("links_menu")
				.addClass("links_float")
				.appendTo($wrapper);

			$(".dropdown__items").hide();
			var $lmenu = $(".lmenu"),
				lmenu_width = $lmenu.outerWidth();

			$(".dropdown").addClass("dropdown_lmenu").each(function(){
				$(this).parent().find(".menu__item-a").first().off("click");
				$(this).parent().off("mouseenter mouseleave").mouseenter(function(){
					$lmenu.appendTo($(".dropdown", this).first());
					//if($(this).offset().left + lmenu_width>ww-15) $lmenu.css("margin-left", ww - 15 - ($(this).offset().left + lmenu_width));
					//else $lmenu.css("margin-left", 0);

					var menu__url = $(".menu__item-a", this).first().attr("href"),
						related_lmenu = $lmenu.find(".lmenu__item-a[href='" + menu__url + "']").first().parent();

					$(".lmenu__item-a").removeClass("lmenu__item-a_open");
					$(".lmenu__item-a", related_lmenu).first().addClass("lmenu__item-a_open");
					$(".lmenu1").hide();
					$(".lmenu1", related_lmenu).show();
					$(".dropdown", this).first().addClass("dropdown_shown");

					$lmenu.css({
						"min-height": $(".lmenu1", related_lmenu).outerHeight()
					});

					$(".menu__item-a", this).first().addClass("menu__item-a_open");
				}).mouseleave(function(){
					$lmenu.appendTo("body");
					$(".dropdown", this).first().removeClass("dropdown_shown");
					$(".menu__item-a", this).first().removeClass("menu__item-a_open");
				});
			});
		} else {
			$links
				.addClass("links_menu")
				.removeClass("links_float")
				.appendTo($menu__others);

			$(".dropdown__items").show();
			$(".dropdown").removeClass("dropdown_lmenu").each(function(){
				$(this).parent().off("mouseenter mouseleave");
				$(this).parent().find(".menu__item-a").first().off("click").click(function(e){
					e.preventDefault();
					var dd = $(this).parent().find(".dropdown").first();
					if(dd.hasClass("dropdown_shown")){
						dd.removeClass("dropdown_shown");
						$(this).first().removeClass("menu__item-a_open");
					} else {
						$(".dropdown_shown").removeClass("dropdown_shown");
						dd.addClass("dropdown_shown");
						$(this).first().addClass("menu__item-a_open");
					}
				});
			});
		}
		/**
		 * выравниваем пункты меню
		 */
		if(ww>768 || menu_shown) {
			$menu.show();
			$menu_spans
				.css("width", "auto")
				//.removeAttr("style")
				.each(function () {
					$(this).data("width", $(this).parent().width());
				})
				.each(function () {
					$(this).width($(this).data("width"));
				})
			;
			rewided = true;
		} else {
			$menu.hide();
		}

		if(ww<=480){
			$(".cats__items-wrap").each(function(){
				$(".cats__items", this).addClass("swiper-wrapper");
				$(".cats__item", this)
					.addClass("swiper-slide")
					.off("mouseenter")
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
				$(".cats__item", this).on("mouseenter", function(e){
					//e.preventDefault();
					cat_info.hide();
					cat_info_title.html($(this).find(".cats__title").html());
					cat_info_img.attr("src", $(this).find(".cats__image img").attr("src"));
					cat_info_benefits.html($(this).data("benefits"));
					cat_info.show();
				}).first().trigger("mouseenter");
			});

			//На десктопах и планшетах параметры камня должны быть справа от картинки
			$(".stone__params-box").insertAfter(".stone__image-box");
		}
	}).trigger("resize");

	$(window).load(function(){
		if($(this).width()>768) {
			//выравниваем высоту половинок в блоке "доставка, монтаж, фотографии"
			$(".dostavka").each(function () {
				var hl = $(".dostavka__box-wrap_left .dostavka__box", this).outerHeight(),
					hr = $(".dostavka__box-wrap_right .dostavka__box", this).outerHeight();
				$(".dostavka__box", this).outerHeight(Math.max(hl, hr));
			});
		}
		//$(window).trigger("resize");
	});

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
		var catalog = this,
			blocks = $(".stones__blocks", this),
			form = $(".stones__form", this),
			popup = $('<div class="stones__popup" />').appendTo("body").hide();

		$("#s_sort, #s_color, #s_group").change(function(){
			$.fancybox.showLoading();
			$.get(form.attr("action"), form.serialize()+'&isNaked=1', function(res){
				blocks.html(res);
				initSwiper(".stones__blocks");
				initStonePreviews();
				$.fancybox.hideLoading();
			});
		});

		popup.mouseenter(function(){
			$(this).addClass("stones__popup_hover");
			//console.log("popup enter");
		}).mouseleave(function(){
			//console.log("popup leave");
			$(this).removeClass("stones__popup_hover");
			setTimeout(function(){
				if(!popup.hasClass("stones__popup_hover")) popup.hide();
			}, 500);
		});
		initStonePreviews();

		function initStonePreviews(){
			$(".stones__item_preview", catalog).mouseenter(function(e){
				if($(window).width()<=768) return true;
				//console.log("item enter");
				var l = $(this).offset().left + 90,
					t = $(this).offset().top + 90,
					w = 660;

				if(l + w>$(window).width() - 10){
					l += $(window).width() - 10 - l - w;
					//popup.css('left', l);
				}
				popup
					.css({
						left: l, //e.pageX,
						top: t
					})
					.addClass("stones__popup_hover")
					.html('<p align="center"><img src="/assets/images/loading.gif" width="64" height="64" alt="Подождите..." /></p>')
					//.appendTo(this)
					.show()
					.load($(this).attr("href")+"?isNaked=1&nc_ctpl=" + ($(this).hasClass("stones__item_plitka") ? 2052 : 2030), function(){

						$(".plitka-order").each(function(){
							var form = this;
							$(".plitka-order__submit", this).click(function(e){
								var comment = 'Интересует: ' + $(form).data("name");
								$("[type=checkbox]:checked", form).each(function(){
									comment += '\n' + $(this).val();
								});
								var zayavka = $(".zayavka");
								if(zayavka.length){
									e.preventDefault();
									var text = $("[name=f_Text]", zayavka),
										val = text.val() + comment + "\n";
									text.val(val);
									$("html, body").animate({
										scrollTop: zayavka.offset().top
									}, 500);
								}
							});
						});
					})
				;
			}).mouseleave(function(){
				//console.log("item leave");
				popup.removeClass("stones__popup_hover");
				setTimeout(function(){
					if(!popup.hasClass("stones__popup_hover")) popup.hide();
				}, 500);
			});
		}
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
	$(".project__order-button, .application__title, .prices__order a").click(function(e){
		var zayavka = $(".zayavka");
		if(zayavka.length){
			e.preventDefault();
			var comment = $(this).data('comment');
			if(!comment) comment = 'Интересует: ' + $(this).html();
			$("[name=f_Text]", zayavka).val(comment);
			if($(this).hasClass("prices__order-a_plitka")){
				$("[name=f_Razmer]", zayavka).val($(this).data("size"));
				$("[name=f_Material]", zayavka)
					.val($(this).data("stone"))
					.siblings("span").first().html($(this).data("stone"))
				;
				$("#tabletop__material", zayavka).attr("checked", "checked");
			}

			$("html, body").animate({
				scrollTop: zayavka.offset().top
			}, 500);
		}
	});

	/**
	 * Шаги производства должны показываться по клику на стрелку рядом
	 */
	$(".steps__item")
		.addClass("steps__item_hidden")
		.first()
		.removeClass("steps__item_hidden")
	;
	$(".steps__arrow").click(function(e){
		e.preventDefault();
		if($(this).closest(".steps__item").hasClass("steps__item_hidden")) return;
		$(this).parent().next(".steps__item").removeClass("steps__item_hidden");
	});

	/**
	 * Внутри каждого блока с преимуществами при клике по ссылке преимущества
	 * видео в блоке должно проигрываться с определенного времени
	 */
	$(".advantages").each(function(){
		var video = $(".advantages__iframe", this),
			w = video.width();
		video.height(Math.round(w/16*10));

		$(".advantages__link", this).click(function(e){
			e.preventDefault();
			var player = video.data("player");
			player.seekTo($(this).data("time"));
			player.playVideo();
		});
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
	 * Специализированные формы заявки работают в два шага
	 * нужно показать первый шаг, а второй и последующие по кнопке "Далее"
	 */
	$(".form__step").hide().filter(".form__step_1").show();
	$(".form__button_next").click(function(e){
		e.preventDefault();
		var step = $(this).closest(".form__step"),
			ok = true;
		step.find("[required]").each(function(){
			if($.trim($(this).val())==""){
				$(this).select().focus();
				ok = false;
				return false;
			}
		});
		if(ok) step.hide().next(".form__step").show();
	});

	/**
	 * Специализированная форма заявки столешниц
	 */
	$(".form_tabletop").each(function(){
		var material = $("[name=f_Material]", this),
			profile = $("[name=f_Torets]", this),
			name = material.data('name')
			;
		$("#tabletop__material", this).click(function(e){
			//e.preventDefault();
			tabletopMaterial1(material.val());
			$(this).prop("checked", "checked");
		});

		$("#memorial_type").change(function(){
			var val = $(this).val();
			if(val=="Фигурный памятник"){
				$("#eskiz_box").show();
			} else {
				$("#eskiz_box").hide();

			}
		}).trigger("change");

		$("#tabletop__profile", this).click(function(){
			$.fancybox({
				href: '/x/tabletop_profile.php?profile=' + encodeURIComponent(profile.val()) + '&name=' + encodeURIComponent(name),
				type: 'ajax',
				padding: $(window).width()>480 ? 50 : 15,
				afterShow: function(){
					initSelectlist(".fancybox-inner");
					$(".form_tabletop-profile").submit(function(e){
						e.preventDefault();
						var profiles = [];
						$("input[type=checkbox]:checked", this).each(function(){
							profiles.push($(this).val());
						});
						profile.val(profiles.join(', '));
						profile.siblings("span").html('(' + profiles.join(', ') + ')');
						$.fancybox.close();
					});
				},
				helpers: {
					overlay: {
						locked: false
					}
				}
			});
			$(this).prop("checked", "checked");
		});

		function tabletopMaterial1(materials){
			$.fancybox({
				href: '/x/tabletop_material.php?material=' + encodeURIComponent(materials) + '&name=' + encodeURIComponent(name),
				type: 'ajax',
				width: 260,
				autoWidth: false,
				autoSize: false,
				maxHeight: 250,
				padding: $(window).width()>480 ? 50 : 15,
				afterShow: function(){
					$(".form__select_material").click(function(e){
						tabletopMaterial2($("#material_text").val());
					});
					$(".form_tabletop-material").submit(function(e){
						e.preventDefault();
						material.val($("#material_text").val());
						material.siblings("span").html('(' + $("#material_text").val() + ')');
						$.fancybox.close();
					});
				},
				helpers: {
					overlay: {
						locked: false
					}
				}
			});
		}
		function tabletopMaterial2(materials){
			$.fancybox({
				href: '/x/tabletop_material2.php?material=' + encodeURIComponent(materials),
				type: 'ajax',
				width: 350,
				autoWidth: false,
				autoSize: false,
				maxHeight: 420,
				padding: $(window).width()>480 ? 50 : 15,
				onCancel: function(){
					tabletopMaterial1(materials);
				},
				tpl: {
					closeBtn: ''
				},
				afterShow: function(){
					initSelectlist(".fancybox-inner");
					$(".form_tabletop-material2").submit(function(e){
						e.preventDefault();
						var materials = [];
						$("input[type=checkbox]:checked", this).each(function(){
							materials.push($(this).val());
						});
						tabletopMaterial1(materials.join(", "));
					});
				},
				helpers: {
					overlay: {
						locked: false
					}
				}
			});
		}
	});

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
	$(".expand_image").addClass("fancybox").attr("rel", "gallery");
	$(".links2__item-a_zvonok").each(function(){
		$(this)
			.addClass("fancybox fancybox.ajax")
			.data("fancybox-title", $(this).html())
			.data("fancybox-href", $(this).attr("href") + "?isNaked=1&f_FromPage=" + encodeURIComponent(location.href));
		;
	});

	$(".links2__item-a_otpravit-zayavku").each(function(){
		$(this)
			.addClass("fancybox fancybox.ajax")
			.data("fancybox-title", $(this).html())
			.data("fancybox-href", $(this).attr("href") + "?isNaked=1&nc_ctpl=2066&f_FromPage=" + encodeURIComponent(location.href));
		;
	});

	$(".fancybox").fancybox({
		padding: 2,
		afterLoad: function(){
			if(this.type=='ajax') this.content = this.content.replace(/\?isNaked=1/,'');
		},
		afterShow: function(){
			$(".fancybox-inner .phone-input").mask("+7 (999) 999-99-99");
			$(".fancybox-inner .form").append('<input type="hidden" name="posting" value="1">');

			/**
			 * Стилизация поля [type=file]
			 */
			$(".fancybox-inner .form__file").each(function(){
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
		},
		helpers: {
			overlay: {
				locked: false
			}
		}
	});

	$(".liquid").each(function(){
		$(this).mouseenter(function(){
			$(this).addClass("liquid_open");
		}).mouseleave(function(){
			$(this).removeClass("liquid_open");
		});
	});

	//var lm0 = 0;
	$(".lmenu1").each(function(){
		//var h = $(this).outerHeight();
		//if(h>lm0) lm0 = h;
		$(this).hide().parent().mouseenter(function(){
			$(".lmenu__item-a", this).first().addClass("lmenu__item-a_open");
			$(".lmenu1", this).show();
			$(this).closest(".lmenu").css({
				"min-height": $(".lmenu1", this).outerHeight()
			});
		}).mouseleave(function(){
			$(".lmenu__item-a", this).first().removeClass("lmenu__item-a_open");
			$(".lmenu1", this).hide();
			$(this).closest(".lmenu").css({
				"min-height": 0
			});
		});
		//$(".lmenu").height(lm0);
	});

});

function initSwiper(prefix){
	var prefix = prefix || "";
	$(prefix + " .swiper").each(function() {
		var p = $(this).parent(),
			slides = $(".swiper-slide", this);
		if(slides.length<2 || p.width()>slides.length*slides.width()) {
			if(slides.hasClass("slider__slide"))
				slides.addClass("swiper-slide-active");
			p.find(".swiper-arrow").hide();
			return;
		}
		var options = {
			autoplay: $(this).data("autoplay"),
			slidesPerView: 'auto',
			centeredSlides: $(this).data("centered"),
			//loopedSlides: $(".swiper-slide", this).length,
			loop: true,
			pagination: p.find('.swiper-bullets'),
			paginationClickable: true
		};
		if($(this).data('noloop') || ($(this).data('minforloop') && slides.length<$(this).data('minforloop')) || ($(this).width()>1.2*slides.width()*slides.length)) options.loop = false;
		if($(this).hasClass("thumbs__slider")) options.direction = 'vertical';

		//if($(this).data("arrows")){
			options.prevButton = p.find(".swiper-arrow--prev");
			options.nextButton = p.find(".swiper-arrow--next");
		//}
		if($(this).hasClass("thumbs__slider")) {
			options.slideToClickedSlide = true;
		}

		var slider = $(this).swiper(options);

		if($(this).hasClass("thumbs__slider")){
			var main_slider = $(this).closest(".slider").find(".slider__wrap").first().data("swiper");
			main_slider.params.control = slider;
			slider.params.control = main_slider;
		}
	});
}

function initSelectlist(prefix){
	var prefix = prefix || "";
	$(prefix + " .selectlist").each(function() {
		var list = this,
			items = $(".selectlist__item", this),
			checks = $("input[type=checkbox]", this);

		items.click(function(){
			$("input[type=checkbox]", this).trigger("click");
		});
		checks.click(function(e){
			e.stopPropagation();
		});
	});
}