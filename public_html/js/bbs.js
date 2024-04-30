$(function () {
  $('input[type=file]').change(function () {
    //$(this)：input[type=file]のこと
    var file = $(this).prop('files')[0];

    // 画像以外は処理を停止
    if (!file.type.match('image.*')) {
      // クリア
      $(this).val('');
      $('.imgfile').html('');
      return;
    }

    // 画像表示
    //FileRaederオブジェクトで非同期に画像をブラウザに表示している
    var reader = new FileReader();
    reader.onload = function () {
      var img_src = $('<img>').attr('src', reader.result);
      $('.imgfile').html(img_src);
      $('.imgarea').removeClass('noimage');
    }
    reader.readAsDataURL(file);
  });


  //Ajaxの処理（リロードしない非同期通信）
  //ajax.phpにデータを飛ばす
  $('.fav__btn').on('click', function () {
    var origin = location.origin;
    var $favbtn = $(this);
    var $threadid = $favbtn.parent().parent().data('threadid');
    var $myid = $('.prof-show').data('me');
    //POST送信
    //date内はajax.phpのchangeFavoriteメソッドと紐付いている
    $.ajax({
      type: 'post',
      url: origin + '/bbs/public_html/ajax.php',
      data: {
        'thread_id': $threadid,
        'user_id': $myid,
      },
      //処理成功
      success: function (data) {
        if (data == 1) {
          //activeクラスを追加する
          $($favbtn).addClass('active');
        } else {
          //activeクラスを削除する
          $($favbtn).removeClass('active');
        }
      }
    });
    return false;
  });

});

