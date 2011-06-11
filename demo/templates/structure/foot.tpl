<!-- Javascript includes -->
`% if $js %`
    `% foreach $js as $j %`<script type="text/javascript" src="`$j`"></script>
    `% endfor %`
`% endif %`
</body>
</html>