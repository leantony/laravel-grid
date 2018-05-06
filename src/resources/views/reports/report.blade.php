<table class="minimalistBlack" style="border-width:3px;border-style:solid;border-color:#000000;width:100%;text-align:left;border-collapse:collapse;" >
    <thead style="background-color:#dbdbdb;background-image:none;background-repeat:repeat;background-position:bottom;background-attachment:scroll;border-bottom-width:3px;border-bottom-style:solid;border-bottom-color:#000000;" >
    <tr>
        @foreach($columns as $column)
            <th style="border-width:1px;border-style:solid;border-color:#000000;padding-top:5px;padding-bottom:5px;padding-right:4px;padding-left:4px;font-size:15px;font-weight:bold;color:#000000;text-align:left;" >
                {{ $column->name }}
            </th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($data as $k => $v)
        <tr>
            @foreach($v as $c)
                <td style="border-width:1px;border-style:solid;border-color:#000000;padding-top:5px;padding-bottom:5px;padding-right:4px;padding-left:4px;font-size:13px;" >
                    {!! $c !!}
                </td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>