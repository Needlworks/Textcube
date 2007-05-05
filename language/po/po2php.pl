#!/usr/bin/perl -w

if( $#ARGV < 0 )
{
	print "Usage: $0 <source po-file> <output php-file>\n";
	exit 1;
}

$source_pofile = $ARGV[0];

if( $#ARGV == 0 )
{
	$target_file = "strings.php";
}
else
{
	$target_file = $ARGV[1];
}

if( $source_pofile eq $target_file )
{
	$target_file = $source_pofile . ".php";
}

print "Source: $source_pofile\n";
print "Output: $target_file\n";

if( ! -f $source_pofile )
{
	print "Source doesn't exist: $source_pofile\n";
	exit 1;
}

sub print_item
{
	my $target = shift;
	my $msgid = shift;
	my $msgstr = shift;

}

$src_line_count = 0;
$state = 0;
open SOURCE, $source_pofile or die;
open TARGET, ">$target_file";
print TARGET << "EOT";
<?php \r
EOT
$comment = "";
while( <SOURCE> )
{
	$src_line_count++;
	chomp;
	if( /^#/ )
	{
		$comment .= "$_\n";
		next;
	}

	if( $state == 0 )
	{
		next unless /^msgid\s+(.+)/;
		$state = 1;
		$msgid = $1;
		next;
	}
	if( $state == 1 )
	{
		if( /^msgstr\s+(.+)/ )
		{
			$msgstr = $1;
			$state = 2;
		}
		else
		{
			$msgid = "$msgid+$_";
		}
		next;
	}
	if( $state == 2 )
	{
		if( /^\s*$/ )
		{
			if( $msgid ne "\"\"" )
			{
				if( !$msgstr && $dupcheck{$msgstr} eq "check" )
				{
					print "$source_pofile:$src_line_count: error: Duplicated value - $msgid\n";
					$error_occurred = 1;
				}
				$msg{$msgid} = $msgstr;
				$comment{$msgid} = $comment;
				$comment = "";
				$dupcheck{$msgstr} = "check";
			}
			$state = 0;
		}
		else
		{
			$msgstr = "$msgstr+$_";
		}
	}
}

foreach( sort keys %msg )
{
	$msgid = $_;
	$msgstr = $msg{$_};
	$comment = $comment{$_};

	next if $msgid eq "\"\"" ;

	$msgid =~ s{"\+"}{}g;
	$msgid =~ s{^"|"$}{'}g;
	$msgstr =~ s{"\+"}{}g;
	$msgstr =~ s{^"|"$}{'}g;

	$msgid =~ s{\\"}{"}g;
	$msgid =~ s{\\\\}{\\}g;
	$msgstr =~ s{\\"}{"}g;
	$msgstr =~ s{\\\\}{\\}g;

	$pass = "";
	$pass = "//" if $msgstr eq "\'\'";

	print TARGET $comment;
	print TARGET $pass . "\$__text[$msgid] = $msgstr;\r\n";
}

print TARGET << "EOT";
?>\r
EOT
close( SOURCE );
close( TARGET );

if( $error_occurred )
{
	exit 1;
}

