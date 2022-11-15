<?php

return [
    'external_enrollment'=>[
        'id_number_group_field'=>env('EXTERNAL_ENROLLMENT_IDNUMBERGROUP_FIELD'),
        'id_number_course_field'=>env('EXTERNAL_ENROLLMENT_IDNUMBERCOURSE_FIELD'),
        'group_name_field'=>env('EXTERNAL_ENROLLMENT_GROUPNAME_FIELD'),
        'tahun_akademik_field'=>env('EXTERNAL_ENROLLMENT_TAHUNAKADEMIK_FIELD'),
        'id_number_user_field'=>env('EXTERNAL_ENROLLMENT_IDNUMBERUSER_FIELD'),
        'executeable_path'=>env('EXTERNAL_ENROLLMENT_EXECUTEABLE_PATH')

    ],
    'clear_cache'=>[
        'executeable_path'=>env('CLEAR_CACHE_EXECUTEABLE_PATH')
    ]

];
