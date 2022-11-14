wr_subject 일정제목


여분필드(추가 변수)의 역할은 아래와 같습니다.
1. wr_1 = 구분
  . ssak=싹산악회,seokuk=서국회,ansi=안시회,jaein=재인회,jaeseo=재서골....
2. wr_2 = 날짜
3. wr_3 = 시작시간
3. wr_4 = 종료시간
4. wr_5 = 장소
...
...
5. wr_9 = 상태
  . pending=대기,checking=확인중,ok=예약완료,trash=삭제



환경설정 (bo_9, bo_8, bo_7)
6. bo_9 = 상태값 설정
  . pending=대기,checking=확인중,ok=예약완료,trash=삭제
3. bo_7/set_default_status = 초기상태값: 예약바로완료, 혹은 대기상태로..
1. bo_7/set_time_unit = 시간설정
  . 1,2,3,4,5,6
5. bo_8 = 구분타입
  . ssak=싹산악회,seokuk=서국회,ansi=안시회,jaein=재인회,jaeseo=재서골....



공휴일 설정 = 달력내부에서 별도로 처리함


