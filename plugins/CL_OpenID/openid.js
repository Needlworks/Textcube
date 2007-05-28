function openid_makeworld()
{
	var openid_title = "";
	var target = "";

	openid_title = openid_id ? openid_id + " logout" : "Login OpenID";
	target = openid_id ? 
				openid_entryurl + "logout?redirect=" + escape(document.location.href) :
				openid_entryurl + "login?redirect=" + escape(document.location.href);

	var inputs = document.getElementsByTagName("input");
	for( var i=0; i<inputs.length; i++ )
	{
		var openid_hint = "OpenID Enabled";
		if( inputs[i].name == "name" && inputs[i].title != openid_hint )
		{
			var openid_pannel = document.createElement("div");
			openid_pannel.innerHTML = "<a style='a:link:none' href=\"" + target + "\"><img style='margin:0; padding:0 0 0 0' align='absmiddle' hspace='2' src=\"" + openid_pluginbase + "openid16x16.gif" + "\"> <span style='padding:-10 0 0 0'>" + openid_title + "</span></a>";
			openid_pannel.innerHTML += " | <a target='_blank' href=\"http://www.google.co.kr/search?q=OpenID&lr=lang_ko\">OpenID란?</a>";
			inputs[i].parentNode.insertBefore( openid_pannel, inputs[i] );

			if( openid_nickname )
			{
				inputs[i].value = openid_nickname;
				inputs[i].readonly = true;
				inputs[i].style.background = "#ffcccc";
			}
			else if( openid_id )
			{
				inputs[i].value = openid_id;
				inputs[i].readonly = true;
				inputs[i].style.background = "#ffcccc";
			}
			inputs[i].title = openid_hint;
		}

		if( inputs[i].name == "homepage" && inputs[i].title != openid_hint )
		{
			if( openid_id )
			{
				inputs[i].value = openid_id;
				inputs[i].title = openid_hint;
			}
		}
		else if( inputs[i].name == "password" )
		{
			if( openid_id )
			{
				inputs[i].value = "";
				inputs[i].disabled = true;
				inputs[i].style.background = "#cccccc";
				inputs[i].title = "OpenID Enabled";
			}
		}
	}
	setTimeout( "openid_makeworld()", 1000 );
}

var test_it = 0;

openid_makeworld();

