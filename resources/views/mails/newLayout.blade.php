<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Confirmación de Reserva</title>

    <!-- CSS Reset -->
    <style>
        /* What it does: Remove spaces around the email design added by some email clients. */
        /* Beware: It can remove the padding / margin and add a background color to the compose a reply window. */
        html,body { margin: 0 auto !important; padding: 0 !important; height: 100% !important; width: 100% !important; }
        /* What it does: Stops email clients resizing small text. */
        * { -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;  }/* What is does: Centers email on Android 4.4 */
        div[style*="margin: 16px 0"] { margin:0 !important; }
        /* What it does: Stops Outlook from adding extra spacing to tables. */
        table, td { mso-table-lspace: 0pt !important; mso-table-rspace: 0pt !important;}
        /* What it does: Fixes webkit padding issue. Fix for Yahoo mail table alignment bug. Applies table-layout to the first 2 tables then removes for anything nested deeper. */
        table { border-spacing: 0 !important; border-collapse: collapse !important; table-layout: fixed !important; margin: 0 auto !important;}
        table table table { table-layout: auto; }
        /* What it does: Uses a better rendering method when resizing images in IE. */
        img { -ms-interpolation-mode:bicubic;}
        /* What it does: A work-around for iOS meddling in triggered links. */
        .mobile-link--footer a, a[x-apple-data-detectors] { color:inherit !important; text-decoration: underline !important;}
    </style>

    <style>
        /* Media Queries */
        .borde{border-right:1px solid #152a69;}
        @media screen and (max-width: 500px) { .borde-email{ border-left: 10px solid #dcdcdd !important; 	border-right: 10px solid #dcdcdd !important;	 }
            .email-container { width: 100% !important; margin: auto !important; }
            /* What it does: Forces elements to resize to the full width of their container. Useful for resizing images beyond their max-width. */
            .fluid { max-width: 100% !important; height: auto !important; margin-left: auto !important; margin-right: auto !important; }
            /* What it does: Forces table cells into full-width rows. */
            .stack-column,
            .stack-column-center { display: block !important; width: 100% !important; max-width: 100% !important; direction: ltr !important; }
            .stack-column-left{ display: block !important; width: 100% !important; max-width: 100% !important; direction: ltr !important; align: left;}
            /* And center justify these ones. */
            .stack-column-center .left { text-align: left !important; }
            /* What it does: Generic utility class for centering. Useful for images, buttons, and nested tables. */
            .center-on-narrow { text-align: center !important; display: block !important; margin-left: auto !important; margin-right: auto !important; float: none !important; border-left: 0px!important; padding-left: 10px!important; padding-right: 10px!important;}
            .left-on-narrow { text-align: left !important; display: block !important; margin-left: auto !important; margin-right: auto !important; float: none !important; }
            .right-on-narrow { text-align: right !important; display: block !important; margin-left: auto !important; margin-right: auto !important; float: none !important; }
            table.center-on-narrow { display: inline-block !important; }
            .onSmall {display:block !important;}
            .hideSmall {display:none !important;}
            .centerMovil { text-align:center !important; }
            .altura{ height: auto!important; padding-bottom: 10px!important; }
            .saltolinea{display: none; mso-line-height-rule: exactly;}
            .show{display:none; mso-hide: all;}
            .f35mov, .f35mov font{font-size:35px !important; line-height:39px !important;}
            .hide{ display: block !important; }
            .borde{border-right:0px solid #152a69 !important;}
            .textcentermv{text-align:center !important; align: center;}
            .bordemv{border-bottom:2px solid #f4f4f4;}
            .movcenter, .movcenter font{text-align:center !important;}
            .pad20mov{padding-left: 20px !important; padding-right:20px !important;}
        }
    </style>

    <!--[if gte mso 9]><xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml><![endif]-->

    <!--[if gte mso 9]>
    <style type="text/css">
        * { -webkit-font-smoothing: antialiased; }
        body { Margin: 0; padding: 0; min-width: 100%; background-color: #dbdbdb; font-family: Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        table { border-spacing: 0; color: #333333; font-family: Arial, sans-serif; }
        img { border: 0; }
        .h1 { font-size: 21px; font-weight: bold; Margin-top: 15px; Margin-bottom: 5px; font-family: Arial, sans-serif; -webkit-font-smoothing: antialiased; }
    </style>
    <![endif]-->
</head>
<body bgcolor="#ffffff" width="100%" style="margin: 0;">

<center style="width: 100%;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="center-on-narrow">
        <tr>
            <td valign="top" align="center">
                <!-- /\/\/\/\/\/\/\/\/\/\ EMAIL BODY : START /\/\/\/\/\/\/\/\/\ -->
                <table role="presentation" cellspacing="0" cellpadding="0" bgcolor="#ffffff" border="0" align="center" width="580" style="margin: auto; width:580px" class="email-container">
                    <tr>
                        <td class="center-on-narrow">
                            <table role="presentation" cellspacing="0" cellpadding="0" bgcolor="#ffffff" border="0" align="center" width="580" style="margin: auto; width:580px" class="email-container">
                                <tr bgcolor="#ffffff">
                                    <td width="90" align="left" class="center-on-narrow" valign="middle" style="vertical-align:middle; padding: 15px 20px 15px 0px;">
                                        @if ($schoolLogo)
                                        <img src="{{ $schoolLogo }}" width="87" height="86" alt="" border="0"  style="display: block;  height: auto; max-width: 87px; max-height:86px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#152a69; line-height:20px; vertical-align:bottom">
                                        @else
                                            <img src="{{ asset('images/logos/boukii.png') }}" width="87" height="86" alt="" border="0"  style="display: block;  height: auto; max-width: 87px; max-height:86px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#152a69; line-height:20px; vertical-align:bottom">
                                        @endif
                                    </td>
                                    <td align="left" class="center-on-narrow" valign="middle" style="vertical-align:middle;">
                                        <font face="Arial, Helvetica, sans-serif" style="font-size:25px; line-height:30px; color:#000000; font-weight:bold;">{{$schoolName}}</font><br>
                                        <font face="Arial, Helvetica, sans-serif" style="font-size:18px; line-height:25px; color:#505050; font-weight:normal;">{{$schoolDescription}}</font>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</center>
@yield('body')
@include($footerView)

</html>
