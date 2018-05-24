<style type="text/css">
    .tg  {border-collapse:collapse;border-spacing:0;border-color:#999;margin:0px auto;}
    .tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:#999;color:#444;background-color:#F7FDFA;}
    .tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:#999;color:#fff;background-color:#26ADE4;}
    .tg .tg-s4m5{font-size:13px;font-family:Arial, Helvetica, sans-serif !important;;vertical-align:top}
    .tg-sort-header::-moz-selection{background:0 0}.tg-sort-header::selection{background:0 0}.tg-sort-header{cursor:pointer}.tg-sort-header:after{content:'';float:right;margin-top:7px;border-width:0 5px 5px;border-style:solid;border-color:#404040 transparent;visibility:hidden}.tg-sort-header:hover:after{visibility:visible}.tg-sort-asc:after,.tg-sort-asc:hover:after,.tg-sort-desc:after{visibility:visible;opacity:.4}.tg-sort-desc:after{border-bottom:none;border-width:5px 5px 0}@media screen and (max-width: 767px) {.tg {width: auto !important;}.tg col {width: auto !important;}.tg-wrap {overflow-x: auto;-webkit-overflow-scrolling: touch;margin: auto 0px;}}</style>
<div class="tg-wrap">
    <h2 style="font-family:Arial, sans-serif; text-align: center; text-decoration: underline;">{{ $title ?? 'Data report' }}</h2>
    <table id="tg-LzT6P" class="tg">
        <tr>
            @foreach($columns as $column)
                <th class="tg-s4m5">
                    {{ $column->name }}
                </th>
            @endforeach
        </tr>
        <tbody>
        @foreach($data as $k => $v)
            <tr>
                @foreach($v as $c)
                    <td>
                        {!! $c !!}
                    </td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<script charset="utf-8">var TGSort=window.TGSort||function(n){"use strict";function r(n){return n.length}function t(n,t){if(n)for(var e=0,a=r(n);e<a;++e)t(n[e],e)}function e(n){return n.split("").reverse().join("")}function a(n){var e=n[0];return t(n,function(n){for(;!n.startsWith(e);)e=e.substring(0,r(e)-1)}),r(e)}function o(n,r){return-1!=n.map(r).indexOf(!0)}var u=parseFloat;function i(n,r){return function(t){var e="";return t.replace(n,function(n,t,a){return e=t.replace(r,"")+"."+(a||"").substring(1)}),u(e)}}var c=i(/^(?:\s*)([+-]?(?:\d+)(?:,\d{3})*)(\.\d*)?$/g,/,/g),s=i(/^(?:\s*)([+-]?(?:\d+)(?:\.\d{3})*)(,\d*)?$/g,/\./g);function f(n){var t=u(n);return!isNaN(t)&&r(""+t)+1>=r(n)?t:NaN}function v(n){var e=[];return t([f,c,s],function(t){var a;r(e)||o(a=n.map(t),isNaN)||(e=a)}),e}function d(n){var t,o,u,i=v(n);return r(i)||(i=v((o=a(t=n),u=a(t.map(e)),t.map(function(n){return n.substring(o,r(n)-u)})))),i}function p(n){var r,e=[];return function n(r,e){e(r),t(r.childNodes,function(r){n(r,e)})}(n,function(n){var t=n.nodeName;"TR"==t?(r=[],e.push(r)):"TD"!=t&&"TH"!=t||r.push(n)}),e}function m(n){if("TABLE"==n.nodeName){var e=p(n),a=r(e),u=a>1&&r(e[0])<r(e[1])?1:0,i=e[u],c=r(i),s=[],f=[];g(function(n,t,e){r(f)<c&&f.push([]);var a=e.textContent||e.innerText||"";f[t].push([a.trim(),e.innerHTML])});var v="tg-sort-asc",m="tg-sort-desc";t(i,function(n,e){s[e]=0;var a=n.classList;a.add("tg-sort-header"),n.addEventListener("click",function(){var n=s[e];!function(){for(var n=0;n<c;++n){var r=i[n].classList;r.remove(v),r.remove(m),s[n]=0}}(),(n=1==n?-1:+!n)&&a.add(n>0?v:m),s[e]=n;var u=[],p=function(r,t){return n*u[r].localeCompare(u[t])||n*(r-t)};var l=[];t(f[e],function(n,r){u.push(n[0]),l.push(r)});var N,h=d(u);(r(h)||r((N=u.map(Date.parse),h=o(N,isNaN)?[]:N)))&&(p=function(r,t){var e=h[r],a=h[t];return e>a?n:e<a?-n:n*(r-t)}),l.sort(p),g(function(n,r,t){t.innerHTML=f[r][l[n]][1]})})})}function g(n){for(var r=u+1;r<a;++r)for(var t=0;t<c;++t)n(r-u-1,t,e[r][t])}}n.addEventListener("DOMContentLoaded",function(){for(var t=n.getElementsByClassName("tg"),e=0;e<r(t);++e)try{m(t[e])}catch(n){}})}(document);</script>