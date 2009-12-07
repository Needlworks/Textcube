(function($) {
	$(document).ready(function(){
		$("#update_body").one('focus',function(){});
		$("#update_body").charCounter(140, {
			format: "%1자 남음",
			format2: "%1자 <span style='color:red;'>초과</span>",
			pulse: false,
			delay: 100
		});

		$(".shortenURLCreate").isShortenURLCreate();
		$(".update_button_mini").isUpdatePostMini();

		if (viewMode == "full") {
			$("#update_button").isUpdatePost();
			$("#pagination #more").isMoreButton();

			$("form#sidebar_search").isSearch(1);
			$("#sidebar_search_submit").isSearch(2);
			$(".saved_searches_list").isSavedSearches();

			if (nowmenu == "search") {
				$("#saved_link").isCreateDestroySavedSearches();
			}

			$(".meta_action .fav-action").isCreateDestroyfavorite();
			$(".meta_action .reply").isReply();
			$(".meta_action .retweet").isReTweet();

			if (nowmenu == "friends" || nowmenu == "my") {
				$(".meta_action .delete").isDestroyStatus();
			}

			if (nowmenu == "direct") {
				isRecipientsListMake();
				$(".meta_action .message").isReply();
				$(".meta_action .delete").isDestroyMessage();
			}

			if (nowmenu == "following" || nowmenu == "followers") {
				if (nowmenu == "followers") {
					$(".meta_action .message").isDirectMessageGo();
				}
				$(".meta_action .follow_act").isCreateDestroyFriendship();
			}

		}
	});
	// update post mini
	$.fn.isUpdatePostMini = function () {
		var pThis = $(".submit_line");
		$(this).click(function () {
			var update_body = $("#update_body");
			var ShortenURL = $("#ShortenURL");
			var update_body_rep = update_body.val().replace(/\r/ig, "").replace(/\n/ig, "");
				var reply_to = $("#in_reply_to_status_id").val();
				if (update_body_rep.length > 0) {
					var requestData =  {body : update_body_rep, reply_to : reply_to, menu : ''};
					var requestURL = blogURL + "/plugin/twitterPostUpdate/";
					var updateID = "";
					$.ajax({
						type : "POST", url : requestURL, dataType : "xml", data : requestData,
						beforeSend : function () {
						},
						success : function (resultXML) {
							updateID = $(resultXML).find("id").text();
							if (updateID != "") {
								PM.showMessage("트위터에 글을 전송했습니다.", "center", "bottom");
								update_body.val("");
								ShortenURL.val("");
							} else {
								PM.showErrorMessage("트위터에 글을 전송하지 못했습니다. 다시 시도해주세요.", "center", "bottom");
							}
							pThis.removeClass("loadingPost");
						},
						error : function () {
							PM.showErrorMessage("트위터에 글을 전송하지 못했습니다. 다시 시도해주세요.", "center", "bottom");
							pThis.removeClass("loadingPost");
						}
					});
				} else {
					PM.showErrorMessage("전송할 글을 입력해주세요.", "center", "bottom");
					update_body.focus();
				}
				return false;
		});
	}

	// update post full
	$.fn.isUpdatePost = function () {
		return this.livequery(function () {
			var pThis = $(".submit_line");
			$(this).click(function () {
				var update_body = $("#update_body");
				var ShortenURL = $("#ShortenURL");
				var update_body_rep = update_body.val().replace(/\r/ig, "").replace(/\n/ig, "");
				if (nowmenu == "direct") {
					var message_to = $("#direct_message_user_id");
					var message_to_text = $('#direct_message_user_id > option[value=' + message_to.val() + ']').text();
					var message_to_id = message_to.val();

					if (message_to_id == "") {
						PM.showErrorMessage("메세지 전송할 사용자를 선택해주세요.", "center", "bottom");
						message_to.focus();
						return false;
					}

					if (update_body_rep.length > 0) {
						var requestData =  {text : update_body_rep, user : message_to_id, menu : nowmenu};
						var requestURL = blogURL + "/plugin/twitterNewMessage/";
						var errorMSG = "";
						$.ajax({
							type : "POST", url : requestURL, dataType : "xml", data : requestData,
							beforeSend : function () {
								pThis.addClass("loadingPost");
							},
							success : function (resultXML) {
								errorMSG = $(resultXML).find("error").text();
								if (errorMSG == "0") {
									PM.showMessage(message_to_text + "님에게 메세지를 전송했습니다.", "center", "bottom");
									if (seleteTab == "sent") {
										$("#timeline").prepend($(resultXML).find("messageStatus").text());
										$(".updatePost").css({"opacity":"0.2"}).animate({opacity: 1.0}, "slow");

										if ($("#timeline").find("li").length > listLength) {
											$("#timeline").find("li:last").remove();
										}
									}
									update_body.val("");
									ShortenURL.val("");
								} else {
									PM.showErrorMessage(message_to_text + "님에게 메세지 전송을 하지못했습니다. 다시 시도해주세요.", "center", "bottom");
								}
								pThis.removeClass("loadingPost");
							},
							error : function () {
								PM.showErrorMessage(message_to_text + "님에게 메세지 전송을 하지못했습니다. 다시 시도해주세요.", "center", "bottom");
								pThis.removeClass("loadingPost");
							}
						});
					} else {
						PM.showErrorMessage(message_to_text + "님에게 전송할 메세지를 입력해주세요.", "center", "bottom");
						update_body.focus();
					}
				} else {
					var reply_to = $("#in_reply_to_status_id").val();
					if (update_body_rep.length > 0) {
						var requestData =  {body : update_body_rep, reply_to : reply_to, menu : nowmenu};
						var requestURL = blogURL + "/plugin/twitterPostUpdate/";
						var updateID = "";
						$.ajax({
							type : "POST", url : requestURL, dataType : "xml", data : requestData,
							beforeSend : function () {
								pThis.addClass("loadingPost");
							},
							success : function (resultXML) {
								updateID = $(resultXML).find("id").text();
								if (updateID != "") {
									PM.showMessage("트위터에 글을 전송했습니다.", "center", "bottom");
									if (nowmenu == "friends" || nowmenu == "my") {
										$("#timeline").prepend($(resultXML).find("updatedStatus").text());
										$(".updatePost").css({"opacity":"0.2"}).animate({opacity: 1.0}, "slow");

										if ($("#timeline").find("li").length > listLength) {
											$("#timeline").find("li:last").remove();
										}
									}
									update_body.val("");
									ShortenURL.val("");
								} else {
									PM.showErrorMessage("트위터에 글을 전송하지 못했습니다. 다시 시도해주세요.", "center", "bottom");
								}
								pThis.removeClass("loadingPost");
							},
							error : function () {
								PM.showErrorMessage("트위터에 글을 전송하지 못했습니다. 다시 시도해주세요.", "center", "bottom");
								pThis.removeClass("loadingPost");
							}
						});
					} else {
						PM.showErrorMessage("전송할 글을 입력해주세요.", "center", "bottom");
						update_body.focus();
					}
				}
				return false;
			});
		});
	}

	// Shorten URL Create
	$.fn.isShortenURLCreate = function () {
		var pThis = $(".shorten_line");
		var tThis = $(".shortenLink");
		$(this).click(function () {
			if (tThis.val().length > 0) {
				if (tThis.val().match(/((http|https|ftp):\/\/[\w?=&.\/-;#~%-]+(?![\w\s?&.\/;#~%"=-]*>))/ig)) {
					var requestData =  {link : tThis.val()};
					var requestURL = blogURL + "/plugin/shortenURLCreate/";
					var errorMSG = "";
					$.ajax({
						type : "POST", url : requestURL, dataType : "xml", data : requestData,
						beforeSend : function () {
							pThis.addClass("loadingLink");
						},
						success : function (resultXML) {
							errorMSG = $(resultXML).find("error").text();
							if (errorMSG == "0") {
								tThis.val($(resultXML).find("shortenURL").text());
								PM.showMessage("짧은 URL을 생성했습니다.", "center", "bottom");
							} else {
								PM.showErrorMessage("짧은 URL을 생성하지 못했습니다. 다시 시도해주세요.", "center", "bottom");
							}
							pThis.removeClass("loadingLink");
						},
						error : function () {
							pThis.removeClass("loadingLink");
							PM.showErrorMessage("짧은 URL을 생성하지 못했습니다. 다시 시도해주세요.", "center", "bottom");
						}
					});
				} else {
					PM.showErrorMessage("링크 형태가 올바르지 못합니다. 다시 시도해주세요.<br /> -> " + tThis.val(), "center", "bottom");
					tThis.val("");
					tThis.focus();
				}
			} else {
				PM.showErrorMessage("짧은 URL을 생성할 링크를 입력해주세요.", "center", "bottom");
				tThis.focus();
			}
		});
	}

	// more page
	$.fn.isMoreButton = function () {
		return this.livequery(function () {
			$(this).click(function () {
				$(this).blur();
				var requestURL = $(this).attr("href");
				var moreOffSet = $(this).offset().top;
				$.ajax({
					type : "GET", url : requestURL, dataType : "xml",
					beforeSend : function () {
						$("#more").addClass("loading").html("");
					},
					success : function (resultXML) {
						$("#timeline").append($(resultXML).find("morePaging").text());
						$("#pagination").html($(resultXML).find("pagination").text());
					},
					error : function () {
						return false;
					}
				});
				return false;
			})
		})
	}

	// search
	$.fn.isSearch = function (key) {
		return this.livequery(function () {
			if (key == 1) {
				$(this).keyup(function(event) {
					if (event.keyCode == 13) {
						var sq = $("#sidebar_search_q");
						var qstr = $.trim(sq.val());
						if (qstr.length < 1) {
							PM.showErrorMessage("검색 키워드를 입력해주세요.", "center", "bottom");
							sq.val('');
							sq.focus();
							return false;
						} else {
							$(this).submit();
						}
					}
				});
			} else if (key == 2) {
				$(this).click(function () {
					$(this).blur();
					var sq = $("#sidebar_search_q");
					var qstr = $.trim(sq.val());
					if (qstr.length < 1) {
						PM.showErrorMessage("검색 키워드를 입력해주세요.", "center", "bottom");
						sq.val('');
						sq.focus();
						return false;
					} else {
						$("form#sidebar_search")[0].submit();
					}
					return false;
				});
			}
		})
	}

	// Saved Searches open or close
	$.fn.isSavedSearches = function () {
		return this.livequery(function () {
			var sThis = $(this);
			$(this).click(function () {
				var sCHK = $(".saved_searches").hasClass("collapsed");
				var sFLAG = sCHK ? "open" : "close";
				var requestData =  {openclose : sFLAG};
				var requestURL = blogURL + "/plugin/twitterSavedSearchesOpenClose/";
				var result = "";
				$.ajax({
					type : "POST", url : requestURL, dataType : "xml", data : requestData,
					beforeSend : function () {
					},
					success : function (resultXML) {
						result = $(resultXML).find("savedSearchesView").text();
						if (result == 'close') {
							$("#my_saved_searches").slideUp("fast");
							$(".saved_searches").addClass("collapsed");
						} else if (result == 'open') {
							$("#my_saved_searches").slideDown("fast");
							$(".saved_searches").removeClass("collapsed");
						}
					},
					error : function () {
						return false;
					}
				});
			})
		})
	}

	// Saved Searches open or close
	$.fn.isSavedSearchesAction = function (sFLAG) {
		return this.livequery(function () {
			var requestData =  {openclose : sFLAG};
			var requestURL = blogURL + "/plugin/twitterSavedSearchesOpenClose/";
			var result = "";
			$.ajax({
				type : "POST", url : requestURL, dataType : "xml", data : requestData,
				beforeSend : function () {
				},
				success : function (resultXML) {
					result = $(resultXML).find("savedSearchesView").text();
					if (result == 'close') {
						$("#my_saved_searches").slideUp("fast");
						$(".saved_searches").addClass("collapsed");
					} else if (result == 'open') {
						$("#my_saved_searches").slideDown("fast");
						$(".saved_searches").removeClass("collapsed");
					}
				},
				error : function () {
					return false;
				}
			});
		})
	}

	// Create or Destroy Saved Searches
	$.fn.isCreateDestroySavedSearches = function () {
		return this.livequery(function () {
			var sThis = $(this);
			var msThis = $("#my_saved_searches");
			var slThis = $("#searchLink");
			$(this).click(function () {
				var sCHK = sThis.hasClass("saveSearchLink");
				var sFLAG = sCHK ? "Create" : "Destroy";
				var sData = sThis.attr((sCHK ? "title" : "rel"));
				var requestData =  {qdata : sData};
				var requestURL = blogURL + "/plugin/twitter" + sFLAG + "SavedSearches/";
				var errorMSG = "";
				var resultID = "";
				var resultQuery = "";
				var resultHtml = "";
				$.ajax({
					type : "POST", url : requestURL, dataType : "xml", data : requestData,
					beforeSend : function () {
						if (sFLAG == "Create") {
							sThis.replaceWith('<span class="loading">Save this search</span>');
						} else {
							sThis.replaceWith('<span class="loading">Remove this saved search</span>');
						}
					},
					success : function (resultXML) {
						errorMSG = $(resultXML).find("error").text();
						resultID = $(resultXML).find("savedID").text();
						resultQuery = $(resultXML).find("savedQuery").text();
						if (errorMSG == "0") {
							if (sFLAG == "Create") {
								slThis.html('<a href="#" id="saved_link" class="deleteSearchLink" rel="' + resultID + '" title="' + resultQuery + '">Remove this saved search</a>');
								resultHtml  = '<li id="search_' + resultID + '" class="search_list">';
								resultHtml += '<a href="' + pluginMenuURL + '&menu=search&q=' + encodeURIComponent(resultQuery) + '" title="' + resultQuery + '" class="selectMenu" >' + resultQuery + '</a>';
								resultHtml += '</li>';
								msThis.append(resultHtml);
								if (msThis.css("display") === "none") {
									msThis.isSavedSearchesAction('open');
								}
							} else {
								slThis.html('<a href="#" id="saved_link" class="saveSearchLink" title="' + resultQuery + '">Save this search</a>');
								$("#search_" + resultID).remove();
							}
						} else {
							PM.showErrorMessage("저장하지 못했습니다. 다시 시도해주세요.", "center", "bottom");

						}
					},
					error : function () {
						return false;
					}
				});
				return false;
			})
		})
	}

	// destroy status
	$.fn.isDestroyStatus = function () {
		return this.livequery(function () {
			var sThis = $(this);
			$(this).click(function () {
				$(this).blur();
				var statusID = $(this).attr("rel");
				var requestData =  {id : statusID};
				var requestURL = blogURL + "/plugin/twitterDestroyStatus/";
				var resultID = "";
				$.ajax({
					type : "POST", url : requestURL, dataType : "xml", data : requestData,
					beforeSend : function () {
						sThis.addClass("fav-throb");
					},
					success : function (resultXML) {
						resultID = $(resultXML).find("statusID").text();
						if (resultID) {
							$("#status_" + resultID).fadeOut("slow").remove();
							$(".statuses").find('li:first-child').addClass("li_first-child");
						}
					},
					error : function () {
						return false;
					}
				});
				return false;
			})
		})
	}

	// create or destroy favorite
	$.fn.isCreateDestroyfavorite = function () {
		return this.livequery(function () {
			var fThis = $(this);
			fThis.click(function () {
				fThis.blur();
				var fID = fThis.attr("id").replace(/favorite_/, "");
				var fCHK = fThis.hasClass("fav");
				var fFLAG = fCHK ? "Destroy" : "Create";
				var requestData =  {id : fID};
				var requestURL = blogURL + "/plugin/twitter" + fFLAG + "Favorites/";
				var resultID = "";
				$.ajax({
					type : "POST", url : requestURL, dataType : "xml", data : requestData,
					beforeSend : function () {
						fThis.attr("title", "").removeClass(fCHK ? "fav" : "non-fav").addClass("fav-throb");
					},
					success : function (resultXML) {
						resultID = $(resultXML).find("favoriteID").text();
						if (resultID) {
							if (nowmenu == "favorites" && fFLAG == "Destroy") {
								$("#status_" + fID).remove();
							} else {
								fThis.attr("title", (fCHK ? "favorite" : "un-favorite") + " this update").removeClass("fav-throb").addClass(fCHK ? "non-fav" : "fav");
							}
						}
					},
					error : function () {
						return false;
					}
				});
				return false;
			})
		})
	}

	// Direct Message menu gogo~~
	$.fn.isDirectMessageGo = function () {
		return this.livequery(function () {
			$(this).click(function () {
				$(this).blur();
				var user_id = $(this).attr("id").replace(/message_user_/, "");
				var menuURL = "";
				menuURL  = pluginMenuURL;
				menuURL += "&menu=direct&seleteTab=sent";
				menuURL += "&user_id=" + user_id;
				window.location.href = menuURL;
				return false;
			})
		})
	}

	// Reply
	$.fn.isReply = function () {
		return this.livequery(function () {
			$(this).click(function () {
				$(this).blur();
				var replyName = $(this).attr("rel");
				var replyStatusID = $(this).attr("id").replace(/reply_/, "");
				if (nowmenu != "direct") {
					var replyStr = "@" + replyName + " ";
					$("#in_reply_to_status_id").val(replyStatusID);
					$('#update_button').val("답변하기");
					$('.doing').html("Reply to " + replyName);
					$("#update_body").val(replyStr);
				} else {
					var DMUID = $("#direct_message_user_id");
					if (!DMUID.find("option[text='" + replyName + "']").attr("selected", true).length) {
						DMUID.append('<option value="' + replyName + '" selected="selected">' + replyName + "</option>")
					}
					$("#update_body").trigger("update");
				}
				$("#update_body").focusEnd('end');
				window.scroll(0, 0);
				return false;
			})
		})
	}

	var isRecipientsListMake = function () {
		var requestURL = blogURL + "/plugin/twitterRecipientsList/";
		$.ajax({
			type : "GET", url : requestURL, dataType : "json",
			beforeSend : function () {
			},
			success : function (jdata) {
				if (jdata) {
					var rOptions = [];
					$.each(jdata, function () {
						var user = this;
						var selecteID = "";
						if ((user.length > 1) && user[0] && user[1]) {
							if (puser_id == user[0]) {
								selecteID = ' selected="selected" ';
							} else {
								selecteID = '';
							}
							rOptions.push('<option value="' + user[0] + '" ' + selecteID + '>' + user[1] + "</option>");
						}
					});
					$("#direct_message_user_id").html('<option value="" ' + (puser_id ? '' : 'selected="selected"') + '></option>' + rOptions.join(""))
				}
			},
			error : function () {
				return false;
			}
		});
	}


	// ReTweet
	$.fn.isReTweet = function () {
		return this.livequery(function () {
			$(this).click(function () {
				$(this).blur();
				var statusID = $(this).attr("id").replace(/retweet_/, "");
				var statusContent = $("#content_" + statusID).text();
				var acountName = $(".acount_name").text();
				var ReTweetName = $(this).attr("rel");
				var ReTweetContent = "";
				ReTweetContent  = "RT @" + ReTweetName + ": ";
				ReTweetContent += statusContent;
				$("#update_body").val(ReTweetContent);
				$("#update_body").focusEnd(3);
				window.scroll(0, 0);
				return false;
			})
		})
	}

	// destroyis message
	$.fn.isDestroyMessage = function () {
		return this.livequery(function () {
			var mThis = $(this);
			$(this).click(function () {
				$(this).blur();
				var DMID = $(this).attr("rel");
				var requestData =  {id : DMID};
				var requestURL = blogURL + "/plugin/twitterDestroyMessage/";
				var resultID = "";
				$.ajax({
					type : "POST", url : requestURL, dataType : "xml", data : requestData,
					beforeSend : function () {
						mThis.addClass("fav-throb");
					},
					success : function (resultXML) {
						resultID = $(resultXML).find("messageID").text();
						if (resultID) {
							$("#status_" + resultID).fadeOut("slow");
						}
					},
					error : function () {
						return false;
					}
				});
				return false;
			})
		})
	}

	// Create or Destroy Friendship
	$.fn.isCreateDestroyFriendship = function () {
		return this.livequery(function () {
			var mThis = $(this);
			$(this).click(function () {
				$(this).blur();
				var userID = $(this).attr("id").replace(/following_/, "").replace(/followers_/, "");
				var fCHK = $(this).hasClass("unfollow");
				var fFLAG = fCHK ? "Destroy" : "Create";
				var requestData = {user_id : userID};
				var requestURL = blogURL + "/plugin/twitter" + fFLAG + "Friendship/";
				var resultID = "";
				var resultBool = "";
				var resultName = "";
				$.ajax({
					type : "POST", url : requestURL, dataType : "xml", data : requestData,
					beforeSend : function () {
						mThis.addClass("fav-throb");
					},
					success : function (resultXML) {
						resultID = $(resultXML).find("id").text();
						resultName = $(resultXML).find("name").text();
						if (resultID && fFLAG == "Destroy") {
							if (nowmenu == "following") {
								$("#user_" + resultID).fadeOut("slow").remove();
								$(".statuses").find('li:first-child').addClass("li_first-child");
							} else if (nowmenu == "followers") {
								$("#followingMSG_" + resultID).addClass("fhide");
								mThis.attr("title", "Follow " + resultName).removeClass("fav-throb").removeClass("unfollow").addClass("follow");
							}
						} else if (resultID && fFLAG == "Create") {
							mThis.attr("title", "Unfollow " + resultName).removeClass("fav-throb").removeClass("follow").addClass("unfollow");
							$("#followingMSG_" + resultID).removeClass("fhide").fadeIn("slow");
						}
					},
					error : function () {
						return false;
					}
				});
				return false;
			})
		})
	}

	// focus end
	$.fn.focusEnd = function (position) {
		return this.each(function () {
			var obj = this;
			if (position == 'end') {
				position = obj.value.length;
			}
			if (obj.style.display != "none") {
				if ($.browser.msie) {
					obj.focus();
					var selRange = obj.createTextRange();
					selRange.collapse(true);
					selRange.moveStart("character", 0);
					selRange.moveEnd("character", position);
					selRange.collapse(false);
					selRange.select()
				} else {
					obj.setSelectionRange(position, position);
					obj.focus()
				}
			}
		})
	};
})(jQuery);