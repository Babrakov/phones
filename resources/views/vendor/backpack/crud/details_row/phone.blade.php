<div class="m-t-10 m-b-10 p-l-10 p-r-10 p-t-10 p-b-10">
	<div class="row">
		<div class="col-md-12">
{{--			<small>Use the <span class="label label-default">details_row</span> functionality to show more information about the entry, when that information does not fit inside the table column.</small><br><br>--}}
			<strong>Телефон:</strong> {{ $entry->vc_phone }} <br>
			<strong>Ф.И.О.:</strong> {{ isset($entry->vc_fio) ? $entry->vc_fio : '' }} <br>
			<strong>Дата рождения:</strong> {{ isset($entry->dt_born) ? date('d.m.Y', strtotime($entry->dt_born)) :'' }} <br>
			<strong>Пол:</strong> {{ isset($entry->sex_id) ? $entry->sex->vc_name : '' }} <br>
{{--			<strong>Регион:</strong> {{ isset($entry->vc_region) ? $entry->vc_region : '' }} <br>--}}
{{--			<strong>Город:</strong> {{ isset($entry->vc_city) ? $entry->vc_city : '' }} <br>--}}
			<strong>Регион:</strong> {{ isset($entry->region_id) ? $entry->region->vc_name : '' }} <br>
			<strong>Город:</strong> {{ isset($entry->town_id) ? $entry->town->vc_name : '' }} <br>
			<strong>Адрес:</strong> {{ isset($entry->tx_location) ? $entry->tx_location : '' }} <br>
			<strong>Email:</strong> {{ isset($entry->vc_email) ? $entry->vc_email : '' }} <br>
{{--			<strong>Источник:</strong> {{ isset($entry->vc_source) ? $entry->vc_source : '' }} <br>--}}
			<strong>Источник:</strong> {{ isset($entry->source_id) ? $entry->source->vc_name : '' }} <br>
            <strong>Дата записи:</strong> {{ $entry->dt_rec }} <br>
            <strong>Ссылка:</strong> {{ isset($entry->vc_link) ? $entry->vc_link : '' }} <br>
            <strong>Примечание:</strong> {{ isset($entry->tx_rem) ? $entry->tx_rem     : '' }} <br>
        </div>
	</div>
</div>
<div class="clearfix"></div>
