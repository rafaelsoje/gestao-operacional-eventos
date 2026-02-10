import '../scss/app.scss';

import $ from 'jquery';
window.$ = window.jQuery = $;

import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import DataTable from 'datatables.net-dt';
DataTable(window, $);

import 'jquery-mask-plugin';

import './legacy/main';
