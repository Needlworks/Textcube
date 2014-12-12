#!/usr/bin/perl -w

if( $#ARGV < 0 )
{
	print "Usage: $0 <source php-file> <output po-file>\n";
	exit 1;
}

$source_file = $ARGV[0];

if( $#ARGV == 0 )
{
	$target_file = "strings.po";
}
else
{
	$target_file = $ARGV[1];
}

if( $source_file eq $target_file )
{
	$target_file = $source_file . ".po";
}

print "Source: $source_file\n";
print "Output: $target_file\n";

if( ! -f $source_file )
{
	print "Source doesn't exist: $source_file\n";
	exit 1;
}

sub print_item
{
	my $target = shift;
	my $msgid = shift;
	my $msgstr = shift;

}

my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = gmtime(time);
$year += 1900;
$mon += 1;
$date = sprintf("%04d-%02d-%02d %02d:%02d:%02d", $year, $mon, $mday, $hour, $min, $sec );

$src_line_count = 0;
open SOURCE, $source_file or die;
open TARGET, ">$target_file";
print TARGET << "EOT";
#,\r
msgid ""\r
msgstr ""\r
"Project-Id-Version: 1.1\\n"\r
"Report-Msgid-Bugs-To: \\n"\r
"POT-Creation-Date: $date+0000\\n"\r
"PO-Revision-Date: $date+0000\\n"\r
"Last-Translator: TEXTCUBE\\n"\r
"Language-Team: TEXTCUBE\\n"\r
"MIME-Version: 1.0\\n"\r
"Content-Type: text/plain; charset=UTF-8\\n"\r
"Content-Transfer-Encoding: 8bit\\n"\r
\r
EOT
$comment = "";
while( <SOURCE> )
{
	$src_line_count++;
	chomp;
	if( /^#/ )
	{
		$comment .= "$_\r\n";
		next;
	}

	if( m/^\$__text\['(.*)'\]\s?=\s?'(.*)';/ )
	{
		$msg{$1} = $2;
		$comment{$1} = $comment;
		$comment = "";
	}
}

foreach( sort keys %msg )
{
	$msgid = $_;
	$msgstr = $msg{$_};
	$comment = $comment{$_};

	$msgid =~ s/\\/\\\\/g;
	$msgid =~ s/"/\\"/g;

	$msgstr =~ s/\\/\\\\/g;
	$msgstr =~ s/"/\\"/g;

	if( $source_file !~ /ko/ && $msgstr eq "" )
	{
#		$msgstr = $msgid;
	}
	print TARGET $comment;
	print TARGET "msgid \"$msgid\"\r\n";
	print TARGET "msgstr \"$msgstr\"\r\n";
	print TARGET "\r\n";
}

close( SOURCE );
close( TARGET );

