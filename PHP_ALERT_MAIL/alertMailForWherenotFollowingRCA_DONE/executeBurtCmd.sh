fileName='report_all'

Date_of_report=$(date +"%Y-%m-%d")
NOW=$(date +"%d%b%Y")
today_date=$(date +"%Y-%m-%d")

time=$(date +"%H:%M:%S")

echo "Start Dtime = $time"

if [ -f "report_all" ]
then
	rm -rf report_all
	rm -rf report_new
	rm -rf report_final
fi

burt report -q'$type =~ /^(CPE)/ && $escal_status =~ /RESOLVED/ && $subtype =~ /cpe_ontap/' -f:'id state escal_status owner mgr_owner call_rec date_create date_closed date_ownermod  subtype dup title' -s 'pri' -xdays > report_all
sed -i '1d' report_all

#burt report -q'$type =~ /^(CPE)/ && $escal_status =~ /RESOLVED/' -f:'id state escal_status owner mgr_owner call_rec title' -s 'pri' -xdays > report_new
#sed -i '1d' report_new

printf "\n" >> $fileName
#printf "\n" >> report_new

#burt view 1404411 | grep -i "ACTIVE -> RESOLVED" -B 1 | grep "\-\- Author:" | grep -o -P ' Date:.*' | awk '{print $7"-"$3"-"$4 }'

# id state escal_status owner mgr_owner call_rec date_create date_closed date_ownermod  subtype dup title
while read line; do
  #owner_name=`echo $line| awk '{print $4 }' `

  burt_id=`echo $line| awk '{print $1 }' `

  #mgr_name=`echo $line| awk '{print $5 }' `
  #if [ ${#mgr_name}  -gt 1 ]; then

  days_create=`echo $line| awk '{print $7 }' `

  burt view $burt_id >burt_details

  rca_done=$(cat burt_details| grep -i "RCA_DONE")

  resolve_date=$(cat burt_details | grep -i "^escal_status" -B 5 | grep -i "ACTIVE -> RESOLVED" -B 5 | grep "\-\- Author:" | grep -o -P ' Date:.*' | tail -1| awk '{print $7"-"$3"-"$4 }')
  if [ ${#resolve_date}  -eq 0 ]; then
	resolve_date=$(cat burt_details | grep -i "^escal_status" -B 5 | grep -i "WAIT_EE_CUST -> RESOLVED" -B 5 | grep "\-\- Author:" | grep -o -P ' Date:.*' | tail -1| awk '{print $7"-"$3"-"$4 }')
  fi
  #resolve_date=`burt view $burt_id | grep -i "ACTIVE -> RESOLVED" -B 1 | grep "\-\- Author:" | grep -o -P ' Date:.*' | awk '{print $7"-"$3"-"$4 }'`

  #resolve_date_details=$(sed -n '/^escal_status/,/ACTIVE$/p' burt_details | tac | grep -m 1 "\-\- Author:" | grep -o -P '(?<=Date: ).*(?=--)' | sed 's/-*//g')

  last5months=130

	if [ ${#rca_done}  -eq 0 ]; then
		if [ $days_create -lt $last5months ]; then
			echo `echo $resolve_date " " $line ` >> report_final 
			echo " ID= $burt_id :  resolve Date = $resolve_date"
		fi
	fi

  
done < $fileName 



echo " EndTime=   $(date +"%H:%M:%S")"