<?php
set_time_limit(120);

//sleep(4);

//Output XML
header("Content-type: text/xml; charset=utf-8");

echo '<?xml version="1.0"?>';

if(isset($_GET['command']) && ($_GET['command'] == 'getRoutes'))
{
?><results>
<route>
<type>car</type>
<distance>98.8 km</distance>
<duration>1 hours and 8 minutes</duration>
<price>1383</price>
</route>
<route>
<type>public_transport</type>
<departure>2014-04-17T16:46</departure>
<arrival>2014-04-17T18:04</arrival>
<transfers>2</transfers>
<duration>1 hours and 18 minutes</duration>
<price>1662</price>
</route>
<route>
<type>public_transport</type>
<departure>2014-04-17T17:01</departure>
<arrival>2014-04-17T18:19</arrival>
<transfers>1</transfers>
<duration>1 hours and 18 minutes</duration>
<price>1662</price>
</route>
<route>
<type>public_transport</type>
<departure>2014-04-17T17:16</departure>
<arrival>2014-04-17T18:34</arrival>
<transfers>2</transfers>
<duration>1 hours and 18 minutes</duration>
<price>1662</price>
</route>
<route>
<type>public_transport</type>
<departure>2014-04-17T16:46</departure>
<arrival>2014-04-17T18:13</arrival>
<transfers>1</transfers>
<duration>1 hours and 27 minutes</duration>
<price>1669</price>
</route>
<meta>
<minPrice>1383</minPrice><maxPrice>1669</maxPrice></meta>
</results><?php
}
else
{
?><results>
<event>
<date><![CDATA[29-05-2014 21:00]]></date>
<town><![CDATA[Amsterdam]]></town>
<tickets><![CDATA[6]]></tickets>
<url><![CDATA[http://www.seatwave.com/tori-amos-tickets/concertgebouw-amsterdam-tickets/29-may-2014/perf/897165?affid=&appid=20133480]]></url>
<time>21:00</time>
<price>13843</price>
<artist>
<name><![CDATA[Tori Amos]]></name>
<birthDate><![CDATA[1963-08-22]]></birthDate>
<desc><![CDATA[American singer]]></desc>
<image><![CDATA[<?='http://'.$_SERVER['HTTP_HOST'];?>/vu/iwa/a4/BeThere/app/cache/Tori_Amos_2007.jpg]]></image>
</artist>
<venue>
<name><![CDATA[Concertgebouw Amsterdam]]></name>
<address><![CDATA[Concertgebouwplein 2-6]]></address>
<city><![CDATA[Amsterdam]]></city>
<country><![CDATA[Nederland]]></country>
</venue>
</event>
<event>
<date><![CDATA[26-05-2014 20:30]]></date>
<town><![CDATA[Rotterdam]]></town>
<tickets><![CDATA[8]]></tickets>
<url><![CDATA[http://www.seatwave.com/tori-amos-tickets/de-doelen-tickets/26-may-2014/perf/900022?affid=&appid=20133480]]></url>
<time>20:30</time>
<price>12448</price>
<artist>
<name><![CDATA[Tori Amos]]></name>
<birthDate><![CDATA[1963-08-22]]></birthDate>
<desc><![CDATA[American singer]]></desc>
<image><![CDATA[<?='http://'.$_SERVER['HTTP_HOST'];?>/vu/iwa/a4/BeThere/app/cache/Tori_Amos_2007.jpg]]></image>
</artist>
<venue>
<name><![CDATA[De Doelen]]></name>
<address><![CDATA[Stationssingel 10]]></address>
<city><![CDATA[Rotterdam]]></city>
<country><![CDATA[Nederland]]></country>
</venue>
</event>
<event>
<date><![CDATA[07-05-2014 18:30]]></date>
<town><![CDATA[Amsterdam]]></town>
<tickets><![CDATA[6]]></tickets>
<url><![CDATA[http://www.seatwave.com/amos-lee-tickets/paradiso-tickets/07-may-2014/perf/891092?affid=&appid=20133480]]></url>
<time>18:30</time>
<price>5876</price>
<artist>
<name><![CDATA[Amos Lee]]></name>
<birthDate><![CDATA[1977-06-22]]></birthDate>
<desc><![CDATA[American singer-songwriter]]></desc>
<image><![CDATA[<?='http://'.$_SERVER['HTTP_HOST'];?>/vu/iwa/a4/BeThere/app/cache/Amos_Lee_shot_by_KK.jpg]]></image>
</artist>
<venue>
<name><![CDATA[Paradiso]]></name>
<address><![CDATA[Weteringschans 6-8]]></address>
<city><![CDATA[Amsterdam]]></city>
<country><![CDATA[Nederland]]></country>
</venue>
</event>
<event>
<date><![CDATA[06-05-2014 18:30]]></date>
<town><![CDATA[Amsterdam]]></town>
<tickets><![CDATA[1]]></tickets>
<url><![CDATA[http://www.seatwave.com/amos-lee-tickets/paradiso-tickets/06-may-2014/perf/898130?affid=&appid=20133480]]></url>
<time>18:30</time>
<price>5478</price>
<artist>
<name><![CDATA[Amos Lee]]></name>
<birthDate><![CDATA[1977-06-22]]></birthDate>
<desc><![CDATA[American singer-songwriter]]></desc>
<image><![CDATA[<?='http://'.$_SERVER['HTTP_HOST'];?>/vu/iwa/a4/BeThere/app/cache/Amos_Lee_shot_by_KK.jpg]]></image>
</artist>
<venue>
<name><![CDATA[Paradiso]]></name>
<address><![CDATA[Weteringschans 6-8]]></address>
<city><![CDATA[Amsterdam]]></city>
<country><![CDATA[Nederland]]></country>
</venue>
</event>
</results><?php
}
?>