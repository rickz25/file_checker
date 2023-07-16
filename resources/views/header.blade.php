<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta charset="utf-8">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="dist/img/checker-green1.png">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('dist/css/font-awesome_6.4.0_css_all.min.css') }}" rel="stylesheet">

    <link href="{{ asset('dist/datatables/css/ajax_libs_twitter-bootstrap_5.3.0-alpha3_css_bootstrap.min.css') }}"
        rel="stylesheet">
    <link href="{{ asset('dist/datatables/css/1.13.5_css_dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('dist/datatables/cssbuttons_2.4.0_css_buttons.bootstrap5.min.css') }}" rel="stylesheet">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="{{ asset('dist/js/jquery-3.7.0.js') }}"></script>
    <script src="{{ asset('dist/js/font-awesome_6.4.0_js_all.min.js') }}"></script>
    <script src="{{ asset('dist/datatables/js/1.13.5_js_jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('dist/datatables/js/1.13.5_js_dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('dist/datatables/js/buttons_2.4.0_js_dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('dist/datatables/js/buttons_2.4.0_js_buttons.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('dist/datatables/js/ajax_libs_jszip_3.1.3_jszip.min.js') }}"></script>
    <script src="{{ asset('dist/datatables/js/buttons_2.4.0_js_buttons.html5.min.js') }}"></script>
    <script src="{{ asset('dist/datatables/js/buttons_2.4.0_js_buttons.colVis.min.js') }}"></script>

    <style>
        /* @import url("{{ asset('dist/css/google-font.css') }}"); */

        h1 {
            font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
            font-size: 35px;
            letter-spacing: 2px;
            word-spacing: 2px;
            color: #157347;
            font-weight: 700;
            text-decoration: none;
            font-style: normal;
            font-variant: normal;
            text-transform: none;
        }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            font-family: 'Lato', sans-serif;
        }

        h2 {
            margin: 50px 0;
        }

        section {
            flex-grow: 1;
        }

        table.dataTable td {
            font-size: .9em !important;
        }

        .file-drop-area {
            position: relative;
            display: flex;
            align-items: center;
            width: 450px;
            max-width: 100%;
            padding: 25px;
            border: 1px dashed rgba(12, 11, 11, 0.4);
            border-radius: 3px;
            transition: 0.2s;

            &.is-active {
                background-color: rgba(255, 255, 255, 0.05);
            }
        }

        .btn-validate {
            width: 450px;
            max-width: 100%;
            height: 40px;
            font-size: 16px;
        }

        .fake-btn {
            flex-shrink: 0;
            background-color: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(8, 7, 7, 0.1);
            border-radius: 3px;
            padding: 8px 15px;
            margin-right: 10px;
            font-size: 12px;
            text-transform: uppercase;
        }

        .file-msg {
            font-size: small;
            font-weight: 300;
            line-height: 1.4;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .file-input {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 100%;
            cursor: pointer;
            opacity: 0;

            &:focus {
                outline: none;
            }
        }

        textarea {
            /* overflow-y: scroll; */
            height: 130px;
            font-size: 15px !important;
            /* resize: none;  */
        }

        footer {
            margin-top: 50px;

            a {
                color: rgba(255, 255, 255, 0.4);
                font-weight: 300;
                font-size: 14px;
                text-decoration: none;

                &:hover {
                    color: white;
                }
            }
        }

        .lds-facebook {
            display: inline-block;
            position: relative;
            width: 80px;
            height: 80px;
        }

        .lds-facebook div {
            display: inline-block;
            position: absolute;
            left: 8px;
            width: 16px;
            background: #157347;
            animation: lds-facebook 1.2s cubic-bezier(0, 0.5, 0.5, 1) infinite;
        }

        .lds-facebook div:nth-child(1) {
            left: 8px;
            animation-delay: -0.24s;
        }

        .lds-facebook div:nth-child(2) {
            left: 32px;
            animation-delay: -0.12s;
        }

        .lds-facebook div:nth-child(3) {
            left: 56px;
            animation-delay: 0;
        }

        @keyframes lds-facebook {
            0% {
                top: 8px;
                height: 64px;
            }

            50%,
            100% {
                top: 24px;
                height: 32px;
            }
        }

        .blured {
            filter: blur(3px);
        }


        /* hhh */

        table.dataTable tbody th, table.dataTable tbody td button {
        background-color: #0D6EFD;
        color: #fff;
        padding: 1px 3px 1px 3px;
        margin: 0px;
        line-height: 1.0;
        vertical-align: middle;
        text-align: center;
        border: 0.5px solid #0D6EFD;
        font-size: 90%;
        outline: none;
        }
    </style>
</head>

<body>
