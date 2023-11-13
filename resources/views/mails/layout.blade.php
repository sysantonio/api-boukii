<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <style>
        /* -------------------------------------
          GLOBAL RESETS
      ------------------------------------- */

        /*All the styling goes here*/

        img {
            border: none;
            -ms-interpolation-mode: bicubic;
            max-width: 100%;
        }

        body {
            font-family: sans-serif;
            -webkit-font-smoothing: antialiased;
            font-size: 12pt;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }

        table {
            border-collapse: separate;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            width: 100%;
        }

        table td {
            font-family: sans-serif;
            font-size: 12pt;
            vertical-align: top;
        }

        /* -------------------------------------
          BODY & CONTAINER
      ------------------------------------- */

        .body {
            background-color: #dedede;
            width: 100%;
        }

        /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
        .container {
            display: block;
            margin: 0 auto !important;
            /* makes it centered */
            max-width: 800px;
            padding: 10px;
            width: 100%;
        }

        /* This should also be a block element, so that it will fill 100% of the .container */
        .content {
            box-sizing: border-box;
            background-color: #dedede;
            display: block;
            margin: 0 auto;
            max-width: 100%;
            padding: 10px;
        }

        /* -------------------------------------
          HEADER, FOOTER, MAIN
      ------------------------------------- */
        .main {
            width: 100%;
        }

        .wrapper {
            box-sizing: border-box;
            padding: 20px;
        }

        .content-block {
            padding-bottom: 10px;
            padding-top: 10px;
        }

        .table .title {
            font-weight: bold;
        }

        .text-muted {
            color: #666;
        }

        .table td {
            min-height: 40px;
            border-bottom: 1px solid #ddd;
            display: table-cell;
            vertical-align: middle;
        }

        table.table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        .footer {
            clear: both;
            margin-top: 10px;
            text-align: center;
            width: 100%;
        }

        .footer td,
        .footer p,
        .footer span,
        .footer a {
            color: #999999;
            font-size: 12px;
            text-align: center;
        }

        /* -------------------------------------
          TYPOGRAPHY
      ------------------------------------- */
        h1,
        h2,
        h3,
        h4 {
            color: #ff3085;
            font-family: sans-serif;
            font-weight: bold;
            line-height: 1.4;
            margin: 0;
            margin-bottom: 10px;
        }

        h1 {
            font-size: 35px;
            font-weight: 300;
            text-align: center;
            text-transform: capitalize;
        }

        h4 {
            font-size: 20px;
            font-weight: 300;
            text-transform: capitalize;
            color: black
        }

        p,
        ul,
        ol {
            font-family: sans-serif;
            font-size: 15px;
            font-weight: normal;
            margin: 0;
            margin-bottom: 15px;
        }

        p li,
        ul li,
        ol li {
            list-style-position: inside;
            margin-left: 5px;
        }

        a {
            color: #ff3085;
            text-decoration: underline;
            font-weight: bold;
        }

        /* -------------------------------------
          BUTTONS
      ------------------------------------- */
        .btn {
            box-sizing: border-box;
            width: 100%;
        }

        .btn>tbody>tr>td {
            padding-bottom: 15px;
        }

        .btn table {
            width: auto;
        }

        .btn table td {
            background-color: #ffffff;
            border-radius: 5px;
            text-align: center;
        }

        .btn a {
            background-color: #f61277;
            border: solid 1px #f61277;
            border-radius: 30px;
            color: #ffffff;
            box-sizing: border-box;
            cursor: pointer;
            display: inline-block;
            font-size: 15px;
            font-weight: bold;
            margin: 0;
            padding: 12px 25px;
            text-decoration: none;
        }

        .btn table td {
            border-radius: 20px;
            text-align: center;
        }

        /* -------------------------------------
          OTHER STYLES THAT MIGHT BE USEFUL
      ------------------------------------- */
        .last {
            margin-bottom: 0;
        }

        .first {
            margin-top: 0;
        }

        .align-center {
            text-align: center;
        }

        .align-right {
            text-align: right;
        }

        .align-left {
            text-align: left;
        }

        .clear {
            clear: both;
        }

        .mt0 {
            margin-top: 0;
        }

        .mb0 {
            margin-bottom: 0;
        }

        .preheader {
            color: transparent;
            display: none;
            height: 0;
            max-height: 0;
            max-width: 0;
            opacity: 0;
            overflow: hidden;
            mso-hide: all;
            visibility: hidden;
            width: 0;
        }

        hr {
            border: 0;
            border-bottom: 1px solid #f6f6f6;
            margin: 20px 0;
        }

        .logo {
            text-align: center;
            padding-bottom: 30px;
            padding-top: 20px;
        }

        .img-footer {
            padding-top: 30px;
            text-align: center;
        }

        table {
            margin: auto;
        }

        ul {
            margin-bottom: 20px;
            padding: 0px;
        }

        .border {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 10px;
            vertical-align: middle;
        }

        .border img {
            vertical-align: middle;
            padding-right: 10px;
        }

        .border-rounded {
            border: 1px solid #ffffff;
            border-radius: 30px;
            padding: 30px;
            background: white;
        }


        /* -------------------------------------
          RESPONSIVE AND MOBILE FRIENDLY STYLES
      ------------------------------------- */
        @media only screen and (max-width: 620px) {
            .body {
                background-color: white;
                width: 100%;
            }

            table[class=body] h1 {
                font-size: 28px !important;
                margin-bottom: 10px !important;
            }

            table[class=body] p,
            table[class=body] ul,
            table[class=body] ol,
            table[class=body] td,
            table[class=body] span,
            table[class=body] a {
                font-size: 12pt !important;
            }

            table[class=body] .wrapper,
            table[class=body] .article {
                padding: 10px !important;
            }

            table[class=body] .content {
                padding: 0 !important;
            }

            table[class=body] .container {
                padding: 0 !important;
                width: 100% !important;
            }

            table[class=body] .main {
                border-left-width: 0 !important;
                border-radius: 0 !important;
                border-right-width: 0 !important;
            }

            table[class=body] .btn table {
                width: 100% !important;
            }

            table[class=body] .btn a {
                width: 100% !important;
            }

            table[class=body] .img-responsive {
                height: auto !important;
                max-width: 100% !important;
                width: auto !important;
            }
        }

        /* -------------------------------------
          PRESERVE THESE STYLES IN THE HEAD
      ------------------------------------- */
        @media all {
            .ExternalClass {
                width: 100%;
            }

            .ExternalClass,
            .ExternalClass p,
            .ExternalClass span,
            .ExternalClass font,
            .ExternalClass td,
            .ExternalClass div {
                line-height: 100%;
            }

            .apple-link a {
                color: inherit !important;
                font-family: inherit !important;
                font-size: inherit !important;
                font-weight: inherit !important;
                line-height: inherit !important;
                text-decoration: none !important;
            }

            #MessageViewBody a {
                color: inherit;
                text-decoration: none;
                font-size: inherit;
                font-family: inherit;
                font-weight: inherit;
                line-height: inherit;
            }

            .btn a:hover {
                background-color: #23203f !important;
                border-color: #23203f !important;
                color: #ffffff !important;
            }
        }
    </style>
</head>

<body>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
        <tr>
            <td class="container">
                <div class="content">
                    <table role="presentation" class="main">
                        <tbody>
                            <tr>
                                <td class="wrapper">
                                    <table role="presentation" cellpadding="0" cellspacing="0"
                                        class="border border-rounded">
                                        <tbody>
                                            <tr>
                                                <td>
                                                @if ($schoolLogo)
                                                <p class="logo"><img src="{{ $schoolLogo }}" width="350" height="auto"></p>
                                                @else
                                                    <p class="logo"><img src="{{ asset('images/logos/boukii.png') }}" width="350" height="auto"></p>
                                                @endif

                                                    @yield('body')
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    @include($footerView)

                </div>
            </td>
        </tr>
    </table>
</body>

</html>
