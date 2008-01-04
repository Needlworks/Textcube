* OpenID Plugin for Textcube

* LICENSE: GPL
* Author: Hojin Choi(coolengineer) <hojin.choi@gmail.com>
* HomePage: http://coolengineer.com/tags/openid
* Release: 2008-01-03(1.6), 2007-05-09(1.0), 2007-02-21(0.9), 2007-01-18(0.1)

Version 1.6:
+ Chg: 모든 기능을 주 코드 영역으로 옮김. 이전 디렉토리/파일들은 사실상 사용하지 않음

Version 1.0:
+ Add: 타임아웃으로 종료될 경우, 자동으로 로그인가능하게함
+ Chg: IdP 아이디대신 제시한 아이디를 오픈아이디로 사용함
+ Add: <label rel="openidlinkbeforethis"> blah </label> 과 같은 태그가 있으면 이름 태그 대신 그 앞에 링크 삽입

Version 0.9:

+ Add: 관리자 메뉴에서 접속 통계를 볼 수 있음.
+ Add: 오픈아이디로 작성한 글 옆에는 딱지를 붙여 줌.
+ Fix: 댓글의 Perma-url에서 로그인 시도하는 경우 오류나는 것 수정.
+ Fix: 로그인 시도 링크를 아이콘에서 길게 바꿈.
+ Chg: 버전을 0.9로 껑충 올림.

Version 0.2:

+ 인증된 세션에 대해서는 댓글 수정/삭제가 바로 되도록 추가함.


HELP!
* OpenID 링크때문에 스킨이 깨집니다.
> input 태그중에 이름부분을 찾아서 그 앞에 넣는 것이 기본 동작입니다. 만약 이 동작이 스킨을 망가뜨리면, 스킨을 편집하셔야합니다.
> 원하는 적당한 위치에 있는 <label> 태그를 찾아 주시고, 그 태그안에 <label rel="openidlinkbeforethis"> Name </label> 과 같은  형식이 되도록 맞춰주세요.
