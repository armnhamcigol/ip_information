<?php
header(\ Content-Type: application/json\);
header(\Access-Control-Allow-Origin: *\);
echo json_encode(array(
  \headers\ => array(
    \User-Agent\ => \[\HTTP_USER_AGENT\] ?? \Unknown\,
    \Accept\ => \[\HTTP_ACCEPT\] ?? \Unknown\
  ),
  \server_info\ => array(
    \REMOTE_ADDR\ => \[\REMOTE_ADDR\] ?? \Unknown\,
    \HTTP_X_FORWARDED_FOR\ => \[\HTTP_X_FORWARDED_FOR\] ?? \Not present\,
    \HTTP_X_REAL_IP\ => \[\HTTP_X_REAL_IP\] ?? \Not present\,
    \REQUEST_METHOD\ => \[\REQUEST_METHOD\] ?? \Unknown\,
    \REQUEST_URI\ => \[\REQUEST_URI\] ?? \Unknown\
  ),
  \zscaler_detected\ => false,
  \forwarded_info\ => array(
    \x_forwarded_for\ => \[\HTTP_X_FORWARDED_FOR\] ?? \Not present\,
    \x_real_ip\ => \[\HTTP_X_REAL_IP\] ?? \Not present\,
    \original_ip\ => \[\REMOTE_ADDR\] ?? \Unknown\
  )
));
?>
