<?php

//require "password.php";
// セッション開始
session_start();

// エラーメッセージの初期化
$errorMessage = "";
$db_hashed_pwd = "";

// ログインボタンが押された場合
if (isset($_POST["login"])) {
  // １．ユーザIDの入力チェック
  if (empty($_POST["userid"])) {
    $errorMessage = "ユーザIDが未入力です。";
  } else if (empty($_POST["password"])) {
    $errorMessage = "パスワードが未入力です。";
  } else if ($_POST["password"] != $_POST["password2"]) {
    $errorMessage = "再入力のパスワードが一致しません。";
  }

  // ２．ユーザIDとパスワードが入力されていたら認証する
  if (!empty($_POST["userid"]) && !empty($_POST["password"]) && ($_POST["password"] == $_POST["password2"])) {
    // mysqlへの接続
    $mysqli = new mysqli('localhost', 'loginsystem', '1234', 'loginsystem');
    if ($mysqli->connect_errno) {
      print('<p>データベースへの接続に失敗しました。</p>' . $mysqli->connect_error);
      exit();
    }

    // データベースの選択
    //$mysqli->select_db($db['loginsystem']);

    // 入力値のサニタイズ
    $username = $mysqli->real_escape_string($_POST["userid"]);
	$password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // クエリの実行
    $query = "SELECT * FROM db_user WHERE name = '" . $username . "'";
    $result = $mysqli->query($query);
    if (!$result) {
      print('クエリーが失敗しました。' . $mysqli->error);
      $mysqli->close();
      exit();
    }

    while ($row = $result->fetch_assoc()) {
      // パスワード(暗号化済み）の取り出し
      $db_hashed_pwd = $row['password'];
    }

    // ３．画面から入力されたパスワードとデータベースから取得したパスワードのハッシュを比較します。
    if ($db_hashed_pwd) {
      // 同名のユーザが既に居る場合は登録不可。
      $errorMessage = "そのIDでは登録できません。";
    }
    else {
	  // ユーザIDの処理。データが存在しない場合は1番からIDを振っていく。
	  $query = "SELECT MAX(id) FROM db_user";
	  $result = $mysqli->query($query);
	  if(!$result){
		print('クエリーが失敗しました。' . $mysqli->error);
		$mysqli->close();
		exit();
	  }
	  $userid = 0;
	  while ($row = $result->fetch_assoc()){
	    $userid = $row['MAX(id)'];
	  }
	  if(!$userid){
	    $userid = 1;
	  }else{
	    $userid++;
	  }
      // 同名のユーザが居ない場合は登録処理を行う。
	  $query = "INSERT INTO db_user (id, name, password) VALUES ('$userid', '$username', '$password')";
	  $result = $mysqli->query($query);
	  if(!$result){
		print('クエリーが失敗しました。' . $mysqli->error);
		$mysqli->close();
		exit();
	  }
	  print("登録しました。<br><a href='login.php'>ログイン画面へ戻る</a>");
	  $mysqli->close();
	  exit();
    }
	
	// データベースの切断
	$mysqli->close();
  } else {
    // 未入力なら何もしない
  } 
} 

?>

<!doctype html>
<html>
  <head>
  <meta charset="UTF-8">
  <title>サンプルアプリケーション</title>
  </head>
  <body>
  <!-- $_SERVER['PHP_SELF']はXSSの危険性があるので、actionは空にしておく -->
  <!--<form id="loginForm" name="loginForm" action="<?php print($_SERVER['PHP_SELF']) ?>" method="POST">-->
  
  <form id="loginForm" name="loginForm" action="" method="POST">
  <fieldset>
  <legend>新規ユーザ登録</legend>
  <div><?php echo $errorMessage ?></div>
  <label for="userid">ユーザID</label><input type="text" id="userid" name="userid" value="">
  <br>
  <label for="password">パスワード</label><input type="password" id="password" name="password" value="">
  <br>
  <label for="password2">再入力</label><input type="password" id="password2" name="password2" value="">
  <input type="submit" id="login" name="login" value="登録">
  </fieldset>
  </form>
  </body>
</html>
