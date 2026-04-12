<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            [
                "name" => "Toán học",
                "slug" => "toan-hoc",
                "category" => "Khoa học tự nhiên",
                "status" => "published",
            ],
            [
                "name" => "Vật lý",
                "slug" => "vat-ly",
                "category" => "Khoa học tự nhiên",
                "status" => "published",
            ],
            [
                "name" => "Hóa học",
                "slug" => "hoa-hoc",
                "category" => "Khoa học tự nhiên",
                "status" => "published",
            ],
            [
                "name" => "Tin học",
                "slug" => "tin-hoc",
                "category" => "Khoa học tự nhiên",
                "status" => "published",
            ],
            [
                "name" => "Ngữ văn",
                "slug" => "ngu-van",
                "category" => "Khoa học xã hội",
                "status" => "published",
            ],
            [
                "name" => "Lịch sử",
                "slug" => "lich-su",
                "category" => "Khoa học xã hội",
                "status" => "published",
            ],
            [
                "name" => "Địa lý",
                "slug" => "dia-ly",
                "category" => "Khoa học xã hội",
                "status" => "published",
            ],
            [
                "name" => "Giáo dục công dân",
                "slug" => "giao-duc-cong-dan",
                "category" => "Khoa học xã hội",
                "status" => "published",
            ],
            [
                "name" => "Tiếng Anh",
                "slug" => "tieng-anh",
                "category" => "Ngoại ngữ",
                "status" => "published",
            ],
            [
                "name" => "Tiếng Nhật",
                "slug" => "tieng-nhat",
                "category" => "Ngoại ngữ",
                "status" => "published",
            ],
            [
                "name" => "Tiếng Hàn",
                "slug" => "tieng-han",
                "category" => "Ngoại ngữ",
                "status" => "published",
            ],
            [
                "name" => "Tiếng Trung",
                "slug" => "tieng-trung",
                "category" => "Ngoại ngữ",
                "status" => "published",
            ],

            [
                "name" => "Lập trình Web",
                "slug" => "lap-trinh-web",
                "category" => "Công nghệ",
                "status" => "published",
            ],
            [
                "name" => "Data Analysis",
                "slug" => "data-analysis",
                "category" => "Công nghệ",
                "status" => "published",
            ],
            [
                "name" => "AI / Machine Learning",
                "slug" => "ai-machine-learning",
                "category" => "Công nghệ",
                "status" => "published",
            ],
            [
                "name" => "Quản lý dự án",
                "slug" => "quan-ly-du-an",
                "category" => "Kinh doanh",
                "status" => "published",
            ],
            [
                "name" => "Sales / Bán hàng",
                "slug" => "sales-ban-hang",
                "category" => "Kinh doanh",
                "status" => "published",
            ],
            [
                "name" => "Digital Marketing",
                "slug" => "digital-marketing",
                "category" => "Marketing",
                "status" => "published",
            ],
            [
                "name" => "SEO",
                "slug" => "seo",
                "category" => "Marketing",
                "status" => "published",
            ],
            [
                "name" => "Content Marketing",
                "slug" => "content-marketing",
                "category" => "Marketing",
                "status" => "published",
            ],
            [
                "name" => "Tài chính cá nhân",
                "slug" => "tai-chinh-ca-nhan",
                "category" => "Tài chính",
                "status" => "published",
            ],
            [
                "name" => "Kế toán doanh nghiệp",
                "slug" => "ke-toan-doanh-nghiep",
                "category" => "Tài chính",
                "status" => "published",
            ],
            [
                "name" => "Giao tiếp & Thuyết trình",
                "slug" => "giao-tiep-thuyet-trinh",
                "category" => "Kỹ năng",
                "status" => "published",
            ],
            [
                "name" => "Quản lý thời gian",
                "slug" => "quan-ly-thoi-gian",
                "category" => "Kỹ năng",
                "status" => "published",
            ],
            [
                "name" => "UI/UX Design",
                "slug" => "ui-ux-design",
                "category" => "Thiết kế",
                "status" => "published",
            ],
            [
                "name" => "Photoshop",
                "slug" => "photoshop",
                "category" => "Thiết kế",
                "status" => "published",
            ],
            [
                "name" => "Video Editing",
                "slug" => "video-editing",
                "category" => "Thiết kế",
                "status" => "published",
            ],
            [
                "name" => "Tuyển dụng (HR)",
                "slug" => "tuyen-dung-hr",
                "category" => "Nhân sự",
                "status" => "published",
            ],
        ];

        Subject::insert($subjects);
    }
}
