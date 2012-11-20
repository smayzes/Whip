`% include /structure/head.tpl %`
<div>
    <h1>An exception occurred!</h1>
	`% if $dev %`
		<ul>
		`% foreach $backtrace as $trace %`
			<li>
				<strong>`$trace.file`</strong>, line `$trace.line`:
				<code>`$trace.function`(<span class="exception-args">`% foreach $trace.args as $i=>$arg %`<span class="exception-arg">`$arg`</span>`% if $i lt {$trace.args|count.minus1} %`, `% endif %``% endforeach %`</span>)</code>
			</li>
		`% endforeach %`
		</ul>
	`% else %`
		<p>
			Please try again.
		</p>
	`% endif %`
</div>
`% include /structure/foot.tpl %`