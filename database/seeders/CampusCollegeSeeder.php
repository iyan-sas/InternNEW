<?php

// database/seeders/CampusCollegeSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campus;
use App\Models\College;

class CampusCollegeSeeder extends Seeder
{
    private const MAP = [
        'Bacolor Campus' => [
            'COLLEGE OF EDUCATION',
            'COLLEGE OF BUSINESS STUDIES',
            'COLLEGE OF COMPUTING STUDIES',
            'COLLEGE OF ENGINEERING AND ARCHITECTURE',
            'COLLEGE OF HOSPITALITY AND TOURISM MANAGEMENT',
            'COLLEGE OF INDUSTRIAL TECHNOLOGY',
            'COLLEGE OF SOCIAL SCIENCES AND PHILOSOPHY',
            'COLLEGE OF ARTS AND SCIENCES',
        ],
        'Lubao Campus' => [
            'Bachelor of Science in Civil Engineering',
            'Bachelor of Science in Business Administration Major In Marketing',
            'Bachelor of Science in Information Technology',
            'Bachelor of Elementary Education Major In General Education',
            'Bachelor of Science in Entrepreneurship',
            'Bachelor of Science in Psychology',
            'Bachelor of Science in Tourism Management',
        ],
        'Apalit Campus' => [
            'Bachelor of Science in Business Administration Major In Marketing',
            'Bachelor of Science in Information Technology',
            'Bachelor of Elementary Education Major In General Education',
            'Bachelor of Science in Hospitality Management',
        ],
        'Candaba Campus' => [
            'Bachelor of Science in Business Administration Major In Marketing',
            'Bachelor of Science in Information Technology',
            'Bachelor of Secondary Education major in Filipino',
            'Bachelor of Science in Entrepreneurship',
            'Bachelor of Elementary Education',
            'Bachelor of Secondary Education major in English',
        ],
        'Mexico Campus' => [
            'Bachelor of Science in Business Administration Major In Marketing',
            'Bachelor of Science in Information Technology',
            'Bachelor of Secondary Education major in Filipino',
            'Bachelor of Science in Accountancy',
            'Bachelor of Elementary Education',
            'Bachelor of Secondary Education major in English',
            'Bachelor in Physical Education',
            'Bachelor of Science in Industrial Technology 
                major in Automotive Technology
                major in Electrical Technology
                major in Food and Service Management
                major in Graphic Technology',
            'Bachelor of Technology and Livelihood Education major in Home Economics',
            'Bachelor of Science in Hospitality Management',
        ],
        'Porac Campus' => [
            'Bachelor of Science in Business Administration Major In Marketing',
            'Bachelor of Science in Information Technology',
            'Bachelor of Elementary Education Major In General Education',
            'Bachelor of Science in Social Work',
        ],
        'Santo Tomas Campus' => [
            'Bachelor of Science in Business Administration Major In Marketing',
            'Bachelor of Science in Information Technology',
            'Bachelor of Elementary Education Major In General Education',
            'Bachelor of Science in Hospitality Management',
        ],
        'San Fernando Extension Campus' => [
            'Bachelor of Science in Business Administration Major In Marketing',
            'Bachelor of Science in Tourism Management',
            'Bachelor of Public Administration',
        ],
    ];

    public function run(): void
    {
        foreach (self::MAP as $campusName => $colleges) {
            $campus = Campus::firstOrCreate(['name' => trim($campusName)]);
            foreach ($colleges as $collegeName) {
                College::firstOrCreate([
                    'campus_id' => $campus->id,
                    'name'      => trim(preg_replace('/\s+/', ' ', $collegeName)),
                ]);
            }
        }
    }
}

