Blogger, MetaWeblog API for Tattertools.

(C) Copyright Hojin Choi, All right reserved.
You can distribute this program under GNU GPL license.

1. 먼저 환경설정의 플러그인 메뉴에서 BlogAPI를 활성화하십시오.

2. 블로깅 툴의 URL 지정위치에 다음 중 하나로 설정하십시오.

	http://YOURDOMAIN/<TT-installpath>/plugin/BlogAPI
	http://YOURDOMAIN/<TT-installpath>/plugin/blogapi

	http://YOURDOMAIN/<TT-installpath>/plugin/BlogAPI/xmlrpc
	http://YOURDOMAIN/<TT-installpath>/plugin/blogapi/xmlrpc

	다중 사용자의 경우 <TT-installpath> 에 자신의 경로를 모두 넣어 주셔야 합니다.

3. 태터툴즈 스킨을 편집하면 자동으로 api 위치를 자동으로 인식시킬 수 있습니다.
	1.0.5 이하에서는 아래 태그를 스킨에 넣으십시오.

<link rel="EditURI" type="application/rsd+xml" title="RSD" href="/<TT-installpath>/plugin/BlogAPI/rsd" />

	Zoundry에서는 Homepage만을 입력함으로 자동으로 xmlrpc 경로를 인식할 수 있습니다.

기능:
1. Blogger API
2. MetaWeblog API
3. 테스트된 클라이언트: writely.com, zoundry, performancing
4. RSD(Really Simple Discovery) 지원
5. ID alias 를 지원합니다. .htaliases 파일에 alias를 관리합니다.
	- FORMAT: <space>는 공백과 탭을 의미합니다.
		shortid<space>longid@youremail.com
		littleone<space>bigone@youremail.com

Versions:
----------------------------------------------------------------------------
* Version 0.9.6 (2006-07-02):
+ New
	- Entry url을 대폭 늘임 (소문자 URL 지원)
	- "Tatter Tools"를 Tattertools로 바꿈
	- .htaliases 를 못읽는 버그 수정
	- blogger api에서 post 후 return 되는 id를 string으로 전송.
	- TEST: Semagic 1.5.8.5 id를 줄여서 접근하는 방법

----------------------------------------------------------------------------
* Version 0.9.5 (2006-06-19):
+ New
	- 다중 사용자 모드 지원
	- 1.0.6 이상에서 rsd 지원 태그 자동 삽입. (Zoundry로 테스트됨)
+ Change
	- bloggerapi.php를 blogger.php로 이름 바꿈.
+ Fix
	- login 오류 메시지에 PHP 오류코드가 있어 제대로된 XML로 전송되지 않는 버그 수정.
----------------------------------------------------------------------------
* Version 0.9.4 (2006-06-17):
+ New
	- 태그 및 분류 지원
	- RSD(Really Simple Discovery) 지원
+ Change
	- 태터툴즈의 플러긴 이벤트를 사용하여 접근하도록 바꿈. 
+ Fix
	- 디렉토리권한 문제로 인하여 동작하지 않는 버그를 해결합니다.

----------------------------------------------------------------------------
* Version 0.9.3 (2006-06-13):
+ New
	- MetaWeblog: metaWeblog.getCategories 추가함.
	- MetaWeblog: Performancing(firefox plugin)을 위해 content 에도 본문을 넣음.
	- TEST: Performancing(firefox plugin)에서 content 에도 본문을 넣어 테스트.

----------------------------------------------------------------------------
* Version 0.9.2 (2006-06-13):
+ New
	- MetaWeblog API 구현 (Writely.com,Zoundry에서 테스트)
	- TEST: Writely.com: Category를 Tag로 취급하여 구현.
	- TEST: Zoundry: Category를 추가할 수 없음. (Zoundry의 Category는 TT의 분류인가?)
+ Change
	- Call/Response 모두 태터툴즈가 제공하는 XMLRPC 클래스를 이용함.
	- 더이상 class_path_parser.php 를 이용하지 않음.
	- Debug file을 .ht 로 시작하도록 변경

----------------------------------------------------------------------------
* Version 0.9.1 (2006-06-10):
+ New
	- 긴 ID에 대하여 alias를 둘 수 있음 (.htaliases)
+ Change
	- Response를 태터툴즈가 제공하는 XMLRPC 클래스를 이용함.
	  아직 xmlrpc 요청사항 parsing은 class_path_parser.php 를 이용.

----------------------------------------------------------------------------
* Version 0.9.0 (2006-06-06):
+ New
	- 최초 공개 버전
	- Blogger API 구현

----------------------------------------------------------------------------
----------------------------------------------------------------------------
----------------------------------------------------------------------------

(C) Copyright Hojin Choi, All right reserved.
You can distribute this program under GNU GPL license.

Author Email: hojin.choi@gmail.com
Home Page: http://coolengineer.com/

* You can use xmlrpc to post articles to Tattertools.

1. Enables BlogAPI plugin in your admin menu.
2. Specify plugin url to your blogging tool. as

	http://YOURDOMAIN/<TT-installpath>/plugin/BlogAPI
	http://YOURDOMAIN/<TT-installpath>/plugin/blogapi

	http://YOURDOMAIN/<TT-installpath>/plugin/BlogAPI/xmlrpc
	http://YOURDOMAIN/<TT-installpath>/plugin/blogapi/xmlrpc

	In multi-user environment <TT-installpath> can be owner's individual path.

3. You can add rsd link in your tatter skin to support automatic discover api.
	Add below tag in your skin html if you have one of version 1.0.5 or below.

<link rel="EditURI" type="application/rsd+xml" title="RSD" href="/<TT-installpath>/plugin/BlogAPI/rsd" />

	Zoundry tries to discover via your home url to get xmlrpc information.

Features:
1. Support Blogger API
2. Support MetaWeblog API
3. Tested in writely.com
   Tested in zoundry (http://www.zoundry.com/)
   Tested in performancing (http://performancing.com/)
4. Support RSD(Really Simple Discovery)
5. ID alias support, you can log in with other id, this feature helps you to use 
   blogging tools which restrict length of id.
   .htaliases file holds aliases and canonical ids.


----------------------------------------------------------------------------
