<table style="font-family:Arial, Helvetica, sans-serif;border-width:1px;border-style:solid;border-color:#1C6EA4;background-color:#EEEEEE;width:100%;text-align:left;border-collapse:collapse;">
    <thead style="background-color:#5592bb;background-image:none;background-repeat:repeat;background-position:bottom;background-attachment:scroll;border-bottom-width:2px;border-bottom-style:solid;border-bottom-color:#444444;">
    <tr>
        @foreach($columns as $column)
            <th style="border-width:1px;border-style:solid;border-color:#AAAAAA;padding-top:3px;padding-bottom:3px;padding-right:2px;padding-left:2px;font-size:15px;font-weight:bold;color:#FFFFFF;text-align:left;border-left-width:2px;border-left-style:solid;border-left-color:#D0E4F5;">
                {{ $column->name }}
            </th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($data as $k => $v)
        <tr>
            @foreach($v as $c)
                <td style="border-width:1px;border-style:solid;border-color:#AAAAAA;padding-top:3px;padding-bottom:3px;padding-right:2px;padding-left:2px;font-size:13px;">
                    {!! $c !!}
                </td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>