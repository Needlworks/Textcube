* 한글에서 번역하는 번역자님께:

1. 번역은 http://poedit.sf/ 에서 poedit를 다운로드하셔서 po 를 수정하시면 됩니다.
2. 주기적으로 배포되는 tattertools_ko.pot 를 poedit의 '카탈로그' 메뉴에서 
   'POT 파일로부터 업데이트'를 통해 작업하는 po 파일에서 읽어 들이면,
   새로이 추가되는 문자열을 확인할 수 있습니다.
3. poedit로 번역이 마무리 되면 웹상에서 바로 확인 할 수 있습니다.
4. po 파일은 해당 php 파일과 비교되어 새로운 것이 있으면 php 파일로 컨버팅됩니다.
5. 단, php 파일은 실행중인 아파치에 쓰기 권한이 있어야합니다. (chmod a+w *.po)
6. 기존의 language/*.php 파일은 language/po/*.php 파일을 읽어 들이기 위한 스텁으로 사용됩니다.

* To translators:
  <TODO>

