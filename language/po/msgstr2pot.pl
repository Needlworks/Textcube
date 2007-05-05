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
$comment = "";
while( <SOURCE> )
{
	chomp;
	$src_line_count++;
	if( /^#/ )
	{
		$comment .= "$_\r\n";
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
			$msgid .= "$_\r\n";
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
			$msgstr .= "$_\r\n";
		}
	}
}

foreach( sort keys %msg )
{
	$msgid = $_;
	$msgstr = $msg{$_};
	$comment = $comment{$_};

	if( $msgstr eq "\"\"" )
	{
		$msgstr = $msgid;
	}
	print TARGET $comment;
	print TARGET "msgid $msgstr\r\n";
	print TARGET "msgstr \"\"\r\n";
	print TARGET "\r\n";
}

close( SOURCE );
close( TARGET );

if( $error_occurred )
{
	exit 1;
}

