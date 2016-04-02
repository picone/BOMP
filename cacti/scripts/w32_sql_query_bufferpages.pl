#!/usr/bin/perl 

use strict;
use Win32::OLE;

my $output_delimeter = " ";
my $argCount = scalar(@ARGV);

my $Win32_Class = "Win32_PerfFormattedData_MSSQLSERVER_SQLServerBufferManager";


#Display help if
if ($argCount == 0) {

my @script_name = split m!\\!, $0;

print <<"END";
Display information in the $Win32_Class class of a computer using Windows Management Instrumentation (WMI).

(The user account running this script must have access to the WMI repository of the target host.)

$script_name[((scalar(@script_name)) - 1)] computer

Parameters:

   computer    - the name of the computer to query

If a comma separated list of rvalues is passed to this script, the results will be returned space delimited.

Consult the Microsoft WMI documentation for more information about Windows Management Instrumentation.
http://msdn.microsoft.com/library/en-us/wmisdk/wmi/wmi_start_page.asp

Example:

   $script_name[((scalar(@script_name)) - 1)] localhost

END

	exit;
}


#Parse through the command-line arguments and display the WMI information.

 WMIMain(@ARGV);


sub	WMIMain(\@) {

	my $computer  = $_[0];
	my $kvalue    = $_[1];
	
	my $WMI_Key     = "Name";

	my $class = "WinMgmts://$computer";
	my $wmi = Win32::OLE->GetObject($class);
	
	my $i = 0;

	if ($wmi) {

			my $properties = "Totalpages,Targetpages,Stolenpages,Reservedpages,Procedurecachepages,Freepages,Databasepages";
			my $computers = $wmi->ExecQuery("SELECT $properties FROM $Win32_Class");


			if (scalar(Win32::OLE::in($computers)) lt "1") {
				print "\n    Check the computer and class name.\n";
				print   "    No information was found on the specified class!\n";
				return;
			}


			foreach my $pc (Win32::OLE::in($computers)) {
				properties($pc,$properties);

			}

	} # if wmi


	else {
		print "Unable to talk to WMI for $computer.\n";
	}
	
}

#Loop through an object's properties.
#Parameters:
#	0 - a reference to the object
#	1 - a single property to lookup

sub	properties($$) {
	my $node = $_[0];
	my $properties = $_[1];
	my $i = 0;

	if ($properties eq '*') {
		foreach ( Win32::OLE::in($node->{Properties_}) ) {
			viewPropertyBrowse($_);
			print "\n";

		}
	}

	else {


	my @properties = split(',', $properties);

	foreach (@properties) {

		$i++;

		if (scalar(@properties) eq "1") {
			viewProperty($node->{Properties_}->{$_});

		}

                elsif (scalar(@properties) gt "1") {
			if ($i gt "1") {
				print "$output_delimeter";
			}

			viewPropertyMulti($node->{Properties_}->{$_});

		}
	}
	}	

}


#Display an object's property.
#Parameters:
#	0 - a reference to the property object

sub viewProperty($$) {
	my $object = $_[0];

		chomp ($object->{Value});
		print "$object->{Value}";

}


sub viewPropertyMulti($$) { 
   my $object = $_[0]; 

      chomp ($object->{Value}); 
      print "$object->{Name}:$object->{Value}";
#      print "$object->{Name}:".1024*$object->{Value}.""; 

} 

sub viewPropertyBrowse($$) { 
   my $object = $_[0]; 

      chomp ($object->{Value}); 
      print "$object->{Name}:$object->{Value}";
#      print "$object->{Name}:".1024*$object->{Value}.""; 

} 

#sub viewPropertyMulti($$) {
#	my $object = $_[0];
#
#		chomp ($object->{Value});
#		print "$object->{Name}:$object->{Value}";
#}

#sub viewPropertyBrowse($$) {
#	my $object = $_[0];
#
#		chomp ($object->{Value});
#		print "$object->{Name}:$object->{Value}";
#}
