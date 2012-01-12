  <div class="overlay-notice-container" id="notice-container" style="display: none;">
    <table cellspacing="0" cellpadding="0"> <tbody>
      <tr> <td>
        <div id="notice">
        </div>
      </td> </tr>
    </tbody> </table>
  </div>

  <?php do_content_for("post_cookie_javascripts") ?>
    <script type="text/javascript">
      var text = Cookie.get("notice");
      if (text) {
        notice(text, true);
        Cookie.remove("notice");
      }
    </script>
  <?php end_content_for() ?>