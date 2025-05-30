CREATE DATABASE IF NOT EXISTS seminar;
USE seminar;

CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE speakers (
    speaker_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    bio TEXT,
    description TEXT,
    photo TEXT
);

CREATE TABLE locations (
    location_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    photo TEXT
);

CREATE TABLE seminars (
    seminar_id INT PRIMARY KEY AUTO_INCREMENT,
    topic VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    location_id INT,
    max_participants INT,
    category VARCHAR(100) NOT NULL,
    FOREIGN KEY (location_id) REFERENCES locations(location_id)
);

CREATE TABLE registrations (
    registration_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    seminar_id INT,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (seminar_id) REFERENCES seminars(seminar_id)
);

CREATE TABLE agenda (
    agenda_id INT PRIMARY KEY AUTO_INCREMENT,
    seminar_id INT,
    speaker_id INT,
    title VARCHAR(200) NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    description TEXT,
    FOREIGN KEY (seminar_id) REFERENCES seminars(seminar_id),
    FOREIGN KEY (speaker_id) REFERENCES speakers(speaker_id)
);

-- Thêm admin
INSERT INTO users (username, password, full_name, role)
VALUES ('admin1', '123456', 'Quản trị viên', 'admin');

-- Thêm 40 user
INSERT INTO users (username, password, full_name)
VALUES 
('user1', '123456', 'Nguyễn Văn A'),
('user2', '123456', 'Trần Thị B'),
('user3', '123456', 'Lê Văn C'),
('user4', '123456', 'Phạm Thị D'),
('user5', '123456', 'Hoàng Văn E'),
('user6', '123456', 'Đỗ Thị F'),
('user7', '123456', 'Bùi Văn G'),
('user8', '123456', 'Ngô Thị H'),
('user9', '123456', 'Dương Văn I'),
('user10', '123456', 'Vũ Thị J'),
('user11', '123456', 'Nguyễn Văn K'),
('user12', '123456', 'Trần Thị L'),
('user13', '123456', 'Lê Văn M'),
('user14', '123456', 'Phạm Thị N'),
('user15', '123456', 'Hoàng Văn O'),
('user16', '123456', 'Đỗ Thị P'),
('user17', '123456', 'Bùi Văn Q'),
('user18', '123456', 'Ngô Thị R'),
('user19', '123456', 'Dương Văn S'),
('user20', '123456', 'Vũ Thị T'),
('user21', '123456', 'Nguyễn Văn U'),
('user22', '123456', 'Trần Thị V'),
('user23', '123456', 'Lê Văn W'),
('user24', '123456', 'Phạm Thị X'),
('user25', '123456', 'Hoàng Văn Y'),
('user26', '123456', 'Đỗ Thị Z'),
('user27', '123456', 'Bùi Văn Â'),
('user28', '123456', 'Ngô Thị Ê'),
('user29', '123456', 'Dương Văn Ô'),
('user30', '123456', 'Vũ Thị Ơ'),
('user31', '123456', 'Nguyễn Văn Ư'),
('user32', '123456', 'Trần Thị Á'),
('user33', '123456', 'Lê Văn Ắ'),
('user34', '123456', 'Phạm Thị Ằ'),
('user35', '123456', 'Hoàng Văn Ặ'),
('user36', '123456', 'Đỗ Thị Ấ'),
('user37', '123456', 'Bùi Văn Ầ'),
('user38', '123456', 'Ngô Thị Ẩ'),
('user39', '123456', 'Dương Văn Ậ'),
('user40', '123456', 'Vũ Thị Đ');

INSERT INTO users (username, password, full_name, role)
VALUES
('user41', '123456', 'Nguyễn Văn Bách', 'user'),
('user42', '123456', 'Trần Thị Cúc', 'user'),
('user43', '123456', 'Lê Minh Dũng', 'user'),
('user44', '123456', 'Phạm Hồng Em', 'user'),
('user45', '123456', 'Hoàng Quốc Phong', 'user'),
('user46', '123456', 'Bùi Ngọc Hân', 'user'),
('user47', '123456', 'Đỗ Khánh Huyền', 'user'),
('user48', '123456', 'Ngô Thanh Tú', 'user'),
('user49', '123456', 'Vũ Phương Linh', 'user'),
('user50', '123456', 'Phan Gia Huy', 'user'),
('user51', '123456', 'Nguyễn Thị Mai', 'user'),
('user52', '123456', 'Trần Anh Quân', 'user'),
('user53', '123456', 'Lê Thị Bích', 'user'),
('user54', '123456', 'Phạm Minh Khoa', 'user'),
('user55', '123456', 'Hoàng Mỹ Linh', 'user'),
('user56', '123456', 'Bùi Thanh Tùng', 'user'),
('user57', '123456', 'Đỗ Lan Anh', 'user'),
('user58', '123456', 'Ngô Văn Nam', 'user'),
('user59', '123456', 'Vũ Ngọc Diệp', 'user'),
('user60', '123456', 'Phan Trường Giang', 'user'),
('user61', '123456', 'Nguyễn Quốc Thái', 'user'),
('user62', '123456', 'Trần Hà My', 'user'),
('user63', '123456', 'Lê Nhật Hào', 'user'),
('user64', '123456', 'Phạm Thu Trang', 'user'),
('user65', '123456', 'Hoàng Văn Hùng', 'user'),
('user66', '123456', 'Bùi Hồng Nhung', 'user'),
('user67', '123456', 'Đỗ Văn Tài', 'user'),
('user68', '123456', 'Ngô Thị Hạnh', 'user'),
('user69', '123456', 'Vũ Minh Tuấn', 'user'),
('user70', '123456', 'Phan Kim Ngân', 'user'),
('user71', '123456', 'Nguyễn Gia Bảo', 'user'),
('user72', '123456', 'Trần Thị Hoa', 'user'),
('user73', '123456', 'Lê Anh Duy', 'user'),
('user74', '123456', 'Phạm Hoàng Yến', 'user'),
('user75', '123456', 'Hoàng Minh Quân', 'user'),
('user76', '123456', 'Bùi Thị Hương', 'user'),
('user77', '123456', 'Đỗ Đức Anh', 'user'),
('user78', '123456', 'Ngô Hải Yến', 'user'),
('user79', '123456', 'Vũ Văn Sơn', 'user'),
('user80', '123456', 'Phan Nhật Minh', 'user'),
('user81', '123456', 'Nguyễn Thị Kim', 'user'),
('user82', '123456', 'Trần Quang Vinh', 'user'),
('user83', '123456', 'Lê Hồng Sơn', 'user'),
('user84', '123456', 'Phạm Thanh Hà', 'user'),
('user85', '123456', 'Hoàng Văn Đức', 'user'),
('user86', '123456', 'Bùi Anh Thư', 'user'),
('user87', '123456', 'Đỗ Trung Hiếu', 'user'),
('user88', '123456', 'Ngô Bảo Ngọc', 'user'),
('user89', '123456', 'Vũ Thị Tuyết', 'user'),
('user90', '123456', 'Phan Thành Đạt', 'user'),
('user91', '123456', 'Nguyễn Bích Ngọc', 'user'),
('user92', '123456', 'Trần Thế Anh', 'user'),
('user93', '123456', 'Lê Quỳnh Chi', 'user'),
('user94', '123456', 'Phạm Văn An', 'user'),
('user95', '123456', 'Hoàng Thuỳ Dung', 'user'),
('user96', '123456', 'Bùi Đức Long', 'user'),
('user97', '123456', 'Đỗ Nhật Nam', 'user'),
('user98', '123456', 'Ngô Trà My', 'user'),
('user99', '123456', 'Vũ Khánh Linh', 'user'),
('user100', '123456', 'Phan Mạnh Hùng', 'user');

INSERT INTO speakers (full_name, bio, description, photo) VALUES
('Nguyễn Văn An', 'Chuyên gia marketing với hơn 10 năm kinh nghiệm.', 'Nguyễn Văn An đã tham gia hàng trăm dự án marketing lớn nhỏ, từ thương mại điện tử đến bán lẻ truyền thống. Anh nổi tiếng với phong cách chia sẻ gần gũi, thực tế và luôn mang đến những kiến thức cập nhật. Trong các buổi diễn thuyết, anh tập trung vào việc giúp khán giả nhận ra tiềm năng bản thân, xây dựng chiến lược hiệu quả và phát triển kỹ năng mềm. Anh từng hợp tác với nhiều thương hiệu nổi tiếng trong nước và quốc tế.', 'speaker1'),
('Trần Thị Bình', 'Nhà sáng lập startup giáo dục nổi bật tại Việt Nam.', 'Trần Thị Bình là gương mặt quen thuộc trong cộng đồng startup Việt. Chị đã trải qua nhiều thất bại trước khi đạt được thành công vang dội. Với đam mê giáo dục và công nghệ, chị sáng lập một nền tảng học trực tuyến giúp hàng ngàn học sinh tiếp cận tri thức. Trong các bài nói chuyện, chị truyền cảm hứng về tinh thần khởi nghiệp, vượt qua khó khăn và giữ lửa đam mê.', 'speaker2'),
('Lê Văn Cường', 'Chuyên gia tài chính, cố vấn đầu tư.', 'Lê Văn Cường nổi tiếng với lối phân tích tài chính sâu sắc nhưng dễ hiểu. Anh từng là giám đốc tài chính tại các tập đoàn đa quốc gia. Những chia sẻ của anh xoay quanh cách quản lý tài chính cá nhân, đầu tư thông minh và định hướng sự nghiệp. Anh cũng thường xuyên xuất hiện trên báo chí và truyền hình để bình luận về các vấn đề kinh tế.', 'speaker3'),
('Phạm Thị Dung', 'Tiến sĩ tâm lý học, tác giả bestseller.', 'Phạm Thị Dung đã xuất bản nhiều cuốn sách bán chạy về tâm lý ứng dụng. Chị đặc biệt quan tâm đến vấn đề sức khỏe tinh thần, cân bằng công việc và cuộc sống. Trong các buổi thuyết trình, chị kết hợp kiến thức học thuật với trải nghiệm thực tế để giúp khán giả hiểu rõ bản thân và cải thiện các mối quan hệ xã hội.', 'speaker4'),
('Hoàng Văn Em', 'Kỹ sư công nghệ, chuyên gia AI.', 'Hoàng Văn Em hiện là trưởng nhóm nghiên cứu trí tuệ nhân tạo tại một công ty công nghệ lớn. Anh có niềm đam mê với việc ứng dụng AI để giải quyết các vấn đề xã hội. Các bài chia sẻ của anh tập trung vào việc làm sao AI có thể giúp cuộc sống con người trở nên tốt đẹp hơn, đồng thời nhấn mạnh trách nhiệm đạo đức khi phát triển công nghệ.', 'speaker5'),
('Đỗ Thị Phương', 'Nhà báo, MC truyền hình.', 'Đỗ Thị Phương là một trong những gương mặt MC nổi bật trên sóng truyền hình. Chị có phong thái chuyên nghiệp, giọng nói truyền cảm và khả năng kết nối khán giả xuất sắc. Các buổi chia sẻ của chị xoay quanh kỹ năng giao tiếp, làm chủ sân khấu và nghệ thuật kể chuyện.', 'speaker6'),
('Bùi Văn Giang', 'Doanh nhân, nhà đầu tư thiên thần.', 'Bùi Văn Giang là nhà đầu tư nổi tiếng trong cộng đồng khởi nghiệp Việt Nam. Anh đã hỗ trợ vốn và tư vấn cho nhiều startup trẻ. Trong các bài thuyết trình, anh thường chia sẻ về hành trình khởi nghiệp, cách gọi vốn thành công và những bài học đắt giá rút ra từ thực tế.', 'speaker7'),
('Ngô Thị Hạnh', 'Nhà hoạt động xã hội, sáng lập tổ chức phi lợi nhuận.', 'Ngô Thị Hạnh dành nhiều năm hoạt động trong lĩnh vực bảo vệ quyền trẻ em và phụ nữ. Chị thường chia sẻ về hành trình làm việc cộng đồng, những thách thức phải đối mặt và cách lan tỏa những giá trị tốt đẹp. Bài nói của chị luôn mang đến cảm giác ấm áp và truyền cảm hứng.', 'speaker8'),
('Dương Văn Ích', 'Kiến trúc sư, nhà thiết kế nội thất.', 'Dương Văn Ích nổi tiếng với các công trình sáng tạo, kết hợp hài hòa giữa yếu tố truyền thống và hiện đại. Anh thường chia sẻ về hành trình sáng tạo, cách tìm ý tưởng và tầm quan trọng của thiết kế đối với cuộc sống.', 'speaker9'),
('Vũ Thị Khuê', 'Bác sĩ, chuyên gia dinh dưỡng.', 'Vũ Thị Khuê có hơn 15 năm kinh nghiệm trong ngành y. Chị luôn nỗ lực mang kiến thức y học đến gần hơn với cộng đồng. Các buổi chia sẻ của chị giúp khán giả hiểu đúng về sức khỏe, dinh dưỡng và cách duy trì lối sống lành mạnh.', 'speaker10'),
('Nguyễn Văn Lâm', 'Giám đốc nhân sự, chuyên gia phát triển con người.', 'Nguyễn Văn Lâm có kinh nghiệm quản lý nhân sự tại các tập đoàn lớn. Anh tập trung chia sẻ về kỹ năng lãnh đạo, phát triển đội nhóm và xây dựng văn hóa doanh nghiệp.', 'speaker11'),
('Trần Thị Mai', 'Chuyên gia đào tạo kỹ năng mềm.', 'Trần Thị Mai nổi tiếng với các khóa đào tạo giao tiếp, thuyết trình và lãnh đạo. Chị mang đến những bài học thực tiễn và dễ áp dụng.', 'speaker12'),
('Lê Văn Nam', 'Nhà nghiên cứu giáo dục.', 'Lê Văn Nam đã thực hiện nhiều dự án cải tiến giáo dục ở vùng sâu vùng xa. Anh chia sẻ về cách đổi mới giáo dục và tạo cơ hội học tập bình đẳng.', 'speaker13'),
('Phạm Thị Oanh', 'Nhà văn, tác giả trẻ.', 'Phạm Thị Oanh nổi bật với giọng văn nhẹ nhàng, sâu lắng. Chị chia sẻ về hành trình viết lách và cảm hứng sáng tác.', 'speaker14'),
('Hoàng Văn Phúc', 'Nhà làm phim, đạo diễn.', 'Hoàng Văn Phúc từng tham gia nhiều liên hoan phim quốc tế. Anh nói về nghệ thuật kể chuyện qua hình ảnh và cách xây dựng nhân vật.', 'speaker15'),
('Đỗ Thị Quỳnh', 'Nhà thiết kế thời trang.', 'Đỗ Thị Quỳnh mang đến những thiết kế trẻ trung, cá tính. Chị chia sẻ về xu hướng thời trang và cách tự tin thể hiện phong cách.', 'speaker16'),
('Bùi Văn Sơn', 'Chuyên gia phát triển phần mềm.', 'Bùi Văn Sơn có kinh nghiệm xây dựng ứng dụng lớn, chia sẻ về kỹ thuật lập trình, quản lý dự án và đổi mới sáng tạo.', 'speaker17'),
('Ngô Thị Trang', 'Nhà hoạt động môi trường.', 'Ngô Thị Trang tham gia nhiều chiến dịch bảo vệ môi trường. Chị truyền cảm hứng về sống xanh và trách nhiệm cộng đồng.', 'speaker18'),
('Dương Văn Út', 'Huấn luyện viên thể hình.', 'Dương Văn Út giúp nhiều người thay đổi hình thể và cải thiện sức khỏe. Anh chia sẻ về luyện tập, chế độ ăn uống và động lực.', 'speaker19'),
('Vũ Thị Xuân', 'Nhạc sĩ, ca sĩ.', 'Vũ Thị Xuân nổi tiếng với những ca khúc giàu cảm xúc. Chị nói về hành trình nghệ thuật, cảm hứng sáng tác và sức mạnh của âm nhạc.', 'speaker20');

INSERT INTO locations (name, address, photo) VALUES
('Hội trường A2 - Học viện CNBCVT', 'Km10, Đường Nguyễn Trãi, Q. Hà Đông, Hà Nội', 'location1'),
('Trung tâm Hội nghị Quốc gia', 'Số 1 Đại lộ Thăng Long, Mễ Trì, Nam Từ Liêm, Hà Nội', 'location2'),
('Nhà hát Lớn Hà Nội', 'Số 1 Tràng Tiền, Q. Hoàn Kiếm, Hà Nội', 'location3'),
('White Palace Phạm Văn Đồng', '108 Phạm Văn Đồng, Q. Thủ Đức, TP. Hồ Chí Minh', 'location4'),
('Gem Center', '8 Nguyễn Bỉnh Khiêm, Q.1, TP. Hồ Chí Minh', 'location5'),
('Adora Center', '431 Hoàng Văn Thụ, Q. Tân Bình, TP. Hồ Chí Minh', 'location6'),
('Trung tâm Hội nghị Riverside Palace', '360D Bến Vân Đồn, Q.4, TP. Hồ Chí Minh', 'location7'),
('Pullman Danang Beach Resort', '101 Võ Nguyên Giáp, Q. Ngũ Hành Sơn, Đà Nẵng', 'location8'),
('Furama Resort Danang', '105 Võ Nguyên Giáp, Q. Ngũ Hành Sơn, Đà Nẵng', 'location9');


INSERT INTO seminars (topic, content, start_time, end_time, location_id, max_participants, category) VALUES
('Hội thảo Cơ hội việc làm 2025', 'Hội thảo cung cấp thông tin về xu hướng tuyển dụng, kỹ năng mềm và cơ hội việc làm tại Việt Nam và quốc tế.', '2025-05-01 08:30:00', '2025-05-01 12:30:00', 1, 80, 'việc làm'),
('Phát triển kỹ năng phỏng vấn', 'Buổi workshop rèn luyện kỹ năng phỏng vấn, viết CV, giao tiếp hiệu quả và tạo ấn tượng với nhà tuyển dụng.', '2025-05-04 09:00:00', '2025-05-04 13:00:00', 2, 70, 'việc làm'),
('Networking chuyên nghiệp', 'Hội thảo chia sẻ cách xây dựng mạng lưới chuyên nghiệp, mở rộng cơ hội nghề nghiệp và phát triển cá nhân.', '2025-05-07 14:00:00', '2025-05-07 17:00:00', 3, 90, 'việc làm'),
('Đổi mới trong tuyển dụng', 'Seminar cập nhật xu hướng tuyển dụng hiện đại, vai trò của AI và tự động hóa trong quy trình tuyển dụng.', '2025-05-10 08:00:00', '2025-05-10 12:00:00', 4, 60, 'việc làm'),
('Khám phá ngành nghề tương lai', 'Chương trình giới thiệu các ngành nghề mới nổi, những kỹ năng quan trọng trong thời đại số.', '2025-05-13 13:00:00', '2025-05-13 18:00:00', 5, 100, 'việc làm'),

('Ứng dụng cơ học lượng tử', 'Buổi thảo luận về các ứng dụng mới của cơ học lượng tử trong đời sống và công nghệ.', '2025-05-16 09:00:00', '2025-05-16 12:00:00', 6, 80, 'vật lý'),
('Vật lý thiên văn và vũ trụ', 'Seminar dành cho người yêu khoa học, giải thích những bí ẩn của vũ trụ và tiến bộ trong nghiên cứu vật lý thiên văn.', '2025-05-19 14:00:00', '2025-05-19 17:30:00', 7, 70, 'vật lý'),
('Vật liệu mới trong công nghiệp', 'Hội thảo chia sẻ về các loại vật liệu tiên tiến, siêu dẫn và ứng dụng thực tiễn.', '2025-05-22 08:30:00', '2025-05-22 13:30:00', 8, 60, 'vật lý'),
('Vật lý hạt và ứng dụng', 'Buổi trình bày về vật lý hạt cơ bản và ứng dụng trong y học, năng lượng.', '2025-05-25 09:00:00', '2025-05-25 12:00:00', 9, 85, 'vật lý'),
('Cách mạng vật lý hiện đại', 'Seminar thảo luận về những khám phá vật lý hiện đại và tiềm năng thay đổi cuộc sống.', '2025-05-28 13:00:00', '2025-05-28 18:00:00', 1, 90, 'vật lý'),

('Ứng dụng sinh học phân tử', 'Hội thảo trình bày ứng dụng sinh học phân tử trong y học, nông nghiệp và công nghệ sinh học.', '2025-06-01 08:00:00', '2025-06-01 11:30:00', 2, 60, 'sinh học và y tế'),
('Công nghệ y tế 4.0', 'Seminar giới thiệu các công nghệ tiên tiến trong y tế như AI, robot phẫu thuật, y học chính xác.', '2025-06-04 09:00:00', '2025-06-04 15:00:00', 3, 75, 'sinh học và y tế'),
('Sức khỏe cộng đồng', 'Buổi chia sẻ về nâng cao sức khỏe cộng đồng, phòng chống dịch bệnh, dinh dưỡng.', '2025-06-07 14:00:00', '2025-06-07 18:00:00', 4, 90, 'sinh học và y tế'),
('Chăm sóc sức khỏe tinh thần', 'Hội thảo giúp hiểu rõ tầm quan trọng của sức khỏe tinh thần, phương pháp cân bằng cuộc sống.', '2025-06-10 08:30:00', '2025-06-10 13:30:00', 5, 55, 'sinh học và y tế'),
('Cập nhật nghiên cứu y học', 'Seminar chia sẻ các nghiên cứu y học nổi bật, xu hướng điều trị mới.', '2025-06-13 09:00:00', '2025-06-13 12:00:00', 6, 65, 'sinh học và y tế'),

('Gây quỹ cộng đồng', 'Hội thảo chia sẻ cách xây dựng chiến dịch gây quỹ, thu hút tài trợ, huy động cộng đồng.', '2025-06-16 13:00:00', '2025-06-16 18:00:00', 7, 80, 'gây quỹ'),
('Chiến lược marketing gây quỹ', 'Buổi học về chiến lược marketing, kể chuyện, kêu gọi tài trợ thành công.', '2025-06-19 08:30:00', '2025-06-19 12:30:00', 8, 70, 'gây quỹ'),
('Quản lý quỹ hiệu quả', 'Seminar tập trung vào quản lý quỹ sau gây quỹ, đảm bảo minh bạch, hiệu quả.', '2025-06-22 14:00:00', '2025-06-22 18:00:00', 9, 90, 'gây quỹ'),
('Gây quỹ sáng tạo', 'Buổi workshop về ý tưởng mới trong gây quỹ, ứng dụng công nghệ số.', '2025-06-25 09:00:00', '2025-06-25 12:30:00', 1, 85, 'gây quỹ'),
('Kết nối cộng đồng gây quỹ', 'Chương trình kết nối các nhà tài trợ, tổ chức phi lợi nhuận để hợp tác lâu dài.', '2025-06-28 13:00:00', '2025-06-28 17:30:00', 2, 75, 'gây quỹ'),

('Nông nghiệp hữu cơ bền vững', 'Hội thảo chia sẻ kỹ thuật canh tác hữu cơ, bảo vệ môi trường, đảm bảo an toàn thực phẩm.', '2025-07-01 08:00:00', '2025-07-01 11:00:00', 3, 60, 'hữu cơ'),
('Xu hướng tiêu dùng hữu cơ', 'Seminar giới thiệu xu hướng tiêu dùng thực phẩm hữu cơ, lợi ích sức khỏe.', '2025-07-04 09:00:00', '2025-07-04 14:00:00', 4, 70, 'hữu cơ'),
('Chứng nhận hữu cơ quốc tế', 'Buổi thảo luận về tiêu chuẩn chứng nhận hữu cơ, quy trình kiểm định.', '2025-07-07 14:00:00', '2025-07-07 18:00:00', 5, 80, 'hữu cơ'),
('Ứng dụng công nghệ trong nông nghiệp hữu cơ', 'Seminar chia sẻ về áp dụng công nghệ cao trong sản xuất hữu cơ.', '2025-07-10 08:30:00', '2025-07-10 12:30:00', 6, 90, 'hữu cơ'),
('Phát triển thị trường hữu cơ Việt Nam', 'Hội thảo bàn về cơ hội, thách thức trong phát triển thị trường hữu cơ nội địa.', '2025-07-13 09:00:00', '2025-07-13 13:00:00', 7, 100, 'hữu cơ');


-- Seminar 1: 2025-05-01 08:30–12:30
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(1, 1, 'Khai mạc hội thảo và giới thiệu chương trình', '2025-05-01 08:30', '2025-05-01 09:00', 'Giới thiệu mục tiêu, nội dung chính của hội thảo việc làm'),
(1, 2, 'Thị trường lao động Việt Nam hiện nay', '2025-05-01 09:00', '2025-05-01 10:30', 'Phân tích xu hướng việc làm, kỹ năng cần thiết'),
(1, 3, 'Giao lưu hỏi đáp', '2025-05-01 10:30', '2025-05-01 12:30', 'Người tham gia đặt câu hỏi, chuyên gia giải đáp thắc mắc');

-- Seminar 2: 2025-05-04 09:00–13:00
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(2, 4, 'Giới thiệu kỹ năng phỏng vấn', '2025-05-04 09:00', '2025-05-04 10:00', 'Những điều cơ bản cần biết khi đi phỏng vấn'),
(2, 5, 'Thực hành viết CV và giao tiếp', '2025-05-04 10:00', '2025-05-04 11:30', 'Hướng dẫn viết CV nổi bật, giao tiếp tự tin'),
(2, 6, 'Mô phỏng phỏng vấn và nhận phản hồi', '2025-05-04 11:30', '2025-05-04 13:00', 'Mô phỏng phỏng vấn thực tế, nhận góp ý từ chuyên gia');

-- Seminar 3: 2025-05-07 14:00–17:00
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(3, 7, 'Chiến lược networking chuyên nghiệp', '2025-05-07 14:00', '2025-05-07 15:00', 'Cách xây dựng và mở rộng mạng lưới chuyên nghiệp'),
(3, 8, 'Giao tiếp và tạo ấn tượng', '2025-05-07 15:00', '2025-05-07 16:00', 'Kỹ năng giao tiếp, xây dựng hình ảnh cá nhân'),
(3, 9, 'Thực hành networking', '2025-05-07 16:00', '2025-05-07 17:00', 'Hoạt động networking thực tế giữa người tham dự');

-- Seminar 4: 2025-05-10 08:00–12:00
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(4, 10, 'Cập nhật xu hướng tuyển dụng', '2025-05-10 08:00', '2025-05-10 09:30', 'Xu hướng mới trong tuyển dụng, vai trò của AI'),
(4, 1, 'Tự động hóa quy trình tuyển dụng', '2025-05-10 09:30', '2025-05-10 11:00', 'Ứng dụng phần mềm và AI trong tuyển dụng'),
(4, 2, 'Hỏi đáp với chuyên gia', '2025-05-10 11:00', '2025-05-10 12:00', 'Chuyên gia giải đáp thắc mắc của khán giả');

-- Seminar 5: 2025-05-13 13:00–18:00
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(5, 3, 'Giới thiệu ngành nghề mới', '2025-05-13 13:00', '2025-05-13 14:30', 'Những ngành nghề mới nổi trong thời đại số'),
(5, 4, 'Kỹ năng cần có trong tương lai', '2025-05-13 14:30', '2025-05-13 16:00', 'Kỹ năng mềm và chuyên môn quan trọng'),
(5, 5, 'Thảo luận mở', '2025-05-13 16:00', '2025-05-13 18:00', 'Chia sẻ quan điểm, giao lưu giữa người tham dự và diễn giả');

-- Seminar 6: 2025-05-16 09:00–12:00
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(6, 11, 'Giới thiệu cơ học lượng tử và ứng dụng', '2025-05-16 09:00', '2025-05-16 10:30', 'Giải thích cơ bản về cơ học lượng tử và cách áp dụng vào đời sống hiện đại'),
(6, 12, 'Ứng dụng cơ học lượng tử trong công nghệ', '2025-05-16 10:30', '2025-05-16 12:00', 'Các ví dụ cụ thể về việc ứng dụng cơ học lượng tử trong công nghệ hiện nay');

-- Seminar 7: 2025-05-19 14:00–17:30
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(7, 13, 'Vũ trụ và vật lý thiên văn', '2025-05-19 14:00', '2025-05-19 15:30', 'Khám phá những bí ẩn của vũ trụ và sự tiến bộ trong nghiên cứu vật lý thiên văn'),
(7, 14, 'Các công trình nghiên cứu vật lý thiên văn gần đây', '2025-05-19 15:30', '2025-05-19 17:30', 'Cập nhật những nghiên cứu mới nhất trong lĩnh vực vật lý thiên văn và các phát hiện quan trọng');

-- Seminar 8: 2025-05-22 08:30–13:30
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(8, 15, 'Giới thiệu các loại vật liệu tiên tiến', '2025-05-22 08:30', '2025-05-22 10:00', 'Tìm hiểu các loại vật liệu tiên tiến, siêu dẫn trong công nghiệp và ứng dụng của chúng'),
(8, 16, 'Ứng dụng vật liệu trong sản xuất công nghiệp', '2025-05-22 10:00', '2025-05-22 11:30', 'Ứng dụng các vật liệu mới trong sản xuất và công nghiệp'),
(8, 17, 'Vật liệu và công nghệ trong tương lai', '2025-05-22 11:30', '2025-05-22 13:30', 'Thảo luận về tương lai của các vật liệu mới và công nghệ tiên tiến');

-- Seminar 9: 2025-05-25 09:00–12:00
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(9, 18, 'Giới thiệu vật lý hạt và ứng dụng', '2025-05-25 09:00', '2025-05-25 10:30', 'Giới thiệu về vật lý hạt cơ bản và các ứng dụng trong y học, năng lượng'),
(9, 19, 'Vật lý hạt và năng lượng', '2025-05-25 10:30', '2025-05-25 12:00', 'Khám phá các ứng dụng vật lý hạt trong ngành năng lượng và y học');

-- Seminar 10: 2025-05-28 13:00–18:00
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(10, 20, 'Cách mạng vật lý hiện đại', '2025-05-28 13:00', '2025-05-28 14:30', 'Khám phá những phát hiện mới trong vật lý hiện đại và tác động đến xã hội'),
(10, 1, 'Vật lý hiện đại và cuộc sống', '2025-05-28 14:30', '2025-05-28 16:00', 'Những ứng dụng thực tế của vật lý hiện đại trong đời sống hàng ngày'),
(10, 2, 'Vật lý hiện đại và tương lai', '2025-05-28 16:00', '2025-05-28 18:00', 'Thảo luận về các khám phá vật lý hiện đại và tiềm năng thay đổi thế giới trong tương lai');

-- Seminar 11: 2025-06-01 08:00–11:30
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(11, 3, 'Giới thiệu về sinh học phân tử', '2025-06-01 08:00', '2025-06-01 09:30', 'Giới thiệu về sinh học phân tử và ứng dụng của nó trong y học và nông nghiệp'),
(11, 4, 'Ứng dụng sinh học phân tử trong y học', '2025-06-01 09:30', '2025-06-01 11:30', 'Ứng dụng sinh học phân tử trong các lĩnh vực như chẩn đoán, điều trị bệnh, và nông nghiệp');

-- Seminar 12: 2025-06-04 09:00–15:00
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(12, 5, 'Công nghệ y tế 4.0 và ứng dụng', '2025-06-04 09:00', '2025-06-04 11:00', 'Giới thiệu về các công nghệ mới trong y tế, từ AI đến robot phẫu thuật'),
(12, 6, 'Y học chính xác và tương lai', '2025-06-04 11:00', '2025-06-04 13:00', 'Khám phá y học chính xác và cách nó thay đổi phương thức điều trị'),
(12, 7, 'Các tiến bộ trong robot phẫu thuật', '2025-06-04 13:00', '2025-06-04 15:00', 'Tìm hiểu về các robot phẫu thuật tiên tiến và ứng dụng của chúng trong phẫu thuật');

-- Seminar 13: 2025-06-07 14:00–18:00
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(13, 8, 'Chăm sóc sức khỏe cộng đồng', '2025-06-07 14:00', '2025-06-07 15:30', 'Khám phá các chiến lược nâng cao sức khỏe cộng đồng và phòng ngừa dịch bệnh'),
(13, 9, 'Phòng ngừa dịch bệnh', '2025-06-07 15:30', '2025-06-07 18:00', 'Chia sẻ về các phương pháp phòng ngừa dịch bệnh và tầm quan trọng của dinh dưỡng');

-- Seminar 14: 2025-06-10 08:30–13:30
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(14, 10, 'Chăm sóc sức khỏe tinh thần', '2025-06-10 08:30', '2025-06-10 10:00', 'Giới thiệu về sự quan trọng của sức khỏe tinh thần và cách duy trì cân bằng trong cuộc sống'),
(14, 11, 'Các phương pháp cải thiện sức khỏe tinh thần', '2025-06-10 10:00', '2025-06-10 11:30', 'Khám phá các phương pháp giúp cải thiện sức khỏe tinh thần như thiền và tâm lý học'),
(14, 12, 'Giải pháp cân bằng cuộc sống và công việc', '2025-06-10 11:30', '2025-06-10 13:30', 'Chia sẻ những phương pháp hiệu quả giúp duy trì cân bằng giữa công việc và cuộc sống');

-- Seminar 15: 2025-06-13 09:00–12:00
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(15, 13, 'Cập nhật nghiên cứu y học', '2025-06-13 09:00', '2025-06-13 10:30', 'Tổng quan về các nghiên cứu y học nổi bật và xu hướng điều trị mới'),
(15, 14, 'Các phương pháp điều trị mới trong y học', '2025-06-13 10:30', '2025-06-13 12:00', 'Giới thiệu các phương pháp điều trị mới như tế bào gốc, liệu pháp gen và các tiến bộ trong điều trị bệnh');

-- Seminar 16: 2025-06-16 13:00–18:00
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(16, 15, 'Chiến lược gây quỹ cộng đồng', '2025-06-16 13:00', '2025-06-16 14:30', 'Tìm hiểu các chiến lược gây quỹ cộng đồng và cách thu hút tài trợ hiệu quả'),
(16, 16, 'Quản lý chiến dịch gây quỹ', '2025-06-16 14:30', '2025-06-16 16:00', 'Chia sẻ cách quản lý chiến dịch gây quỹ, duy trì sự hỗ trợ từ cộng đồng'),
(16, 17, 'Ứng dụng công nghệ trong gây quỹ', '2025-06-16 16:00', '2025-06-16 18:00', 'Khám phá cách công nghệ và nền tảng số giúp nâng cao hiệu quả gây quỹ');

-- Seminar 17: 2025-06-19 08:30–12:30
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(17, 18, 'Marketing và gây quỹ', '2025-06-19 08:30', '2025-06-19 10:00', 'Chia sẻ các chiến lược marketing giúp thu hút tài trợ và xây dựng chiến dịch gây quỹ thành công'),
(17, 19, 'Kể chuyện trong marketing gây quỹ', '2025-06-19 10:00', '2025-06-19 12:30', 'Tìm hiểu cách sử dụng kể chuyện trong marketing gây quỹ để thu hút sự chú ý và sự ủng hộ từ cộng đồng');

-- Seminar 18: 2025-06-22 14:00–18:00
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(18, 20, 'Quản lý quỹ gây quỹ hiệu quả', '2025-06-22 14:00', '2025-06-22 15:30', 'Hướng dẫn quản lý quỹ sau khi gây quỹ thành công và đảm bảo minh bạch tài chính'),
(18, 1, 'Kiểm tra và đảm bảo hiệu quả gây quỹ', '2025-06-22 15:30', '2025-06-22 18:00', 'Chia sẻ về cách đánh giá và kiểm tra hiệu quả của chiến dịch gây quỹ');

-- Seminar 19: 2025-06-25 09:00–12:30
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(19, 2, 'Gây quỹ sáng tạo', '2025-06-25 09:00', '2025-06-25 10:30', 'Khám phá những ý tưởng mới và sáng tạo trong việc gây quỹ cộng đồng'),
(19, 3, 'Ứng dụng công nghệ số trong gây quỹ', '2025-06-25 10:30', '2025-06-25 12:30', 'Chia sẻ về các nền tảng công nghệ số giúp nâng cao hiệu quả và sáng tạo trong chiến dịch gây quỹ');

-- Seminar 20: 2025-06-28 13:00–17:30
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(20, 4, 'Kết nối cộng đồng gây quỹ', '2025-06-28 13:00', '2025-06-28 14:30', 'Chia sẻ về cách kết nối nhà tài trợ, tổ chức phi lợi nhuận và cộng đồng trong chiến dịch gây quỹ'),
(20, 5, 'Chiến lược xây dựng mối quan hệ trong gây quỹ', '2025-06-28 14:30', '2025-06-28 17:30', 'Giới thiệu các chiến lược xây dựng mối quan hệ lâu dài với nhà tài trợ và các tổ chức phi lợi nhuận');

-- Seminar 21: 2025-07-01 08:00–11:00
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(21, 6, 'Canh tác hữu cơ bền vững', '2025-07-01 08:00', '2025-07-01 09:30', 'Chia sẻ về các kỹ thuật canh tác hữu cơ bền vững, bảo vệ môi trường và an toàn thực phẩm'),
(21, 7, 'Các mô hình nông nghiệp hữu cơ bền vững', '2025-07-01 09:30', '2025-07-01 11:00', 'Khám phá các mô hình nông nghiệp hữu cơ bền vững tại Việt Nam và trên thế giới');

-- Seminar 22: 2025-07-04 09:00–14:00
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(22, 8, 'Xu hướng tiêu dùng thực phẩm hữu cơ', '2025-07-04 09:00', '2025-07-04 11:00', 'Tìm hiểu về xu hướng tiêu dùng thực phẩm hữu cơ và lợi ích đối với sức khỏe người tiêu dùng'),
(22, 9, 'Tác động của tiêu dùng hữu cơ đến sức khỏe', '2025-07-04 11:00', '2025-07-04 14:00', 'Khám phá các nghiên cứu và dữ liệu về tác động của tiêu dùng thực phẩm hữu cơ đối với sức khỏe');

-- Seminar 23: 2025-07-07 14:00–18:00
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(23, 10, 'Chứng nhận hữu cơ quốc tế', '2025-07-07 14:00', '2025-07-07 15:30', 'Tìm hiểu về các tiêu chuẩn chứng nhận hữu cơ quốc tế và quy trình kiểm định hữu cơ'),
(23, 11, 'Quy trình chứng nhận và kiểm định hữu cơ', '2025-07-07 15:30', '2025-07-07 18:00', 'Chia sẻ chi tiết về quy trình chứng nhận và kiểm định cho sản phẩm hữu cơ tại các tổ chức quốc tế');

-- Seminar 24: 2025-07-10 08:30–12:30
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(24, 12, 'Ứng dụng công nghệ trong nông nghiệp hữu cơ', '2025-07-10 08:30', '2025-07-10 10:00', 'Giới thiệu các công nghệ cao đang được áp dụng trong nông nghiệp hữu cơ để nâng cao năng suất và chất lượng sản phẩm'),
(24, 13, 'Công nghệ tiên tiến trong sản xuất hữu cơ', '2025-07-10 10:00', '2025-07-10 12:30', 'Khám phá các công nghệ tiên tiến và phương pháp mới giúp sản xuất nông sản hữu cơ hiệu quả và bền vững');

-- Seminar 25: 2025-07-13 09:00–13:00
INSERT INTO agenda (seminar_id, speaker_id, title, start_time, end_time, description) VALUES
(25, 14, 'Phát triển thị trường hữu cơ Việt Nam', '2025-07-13 09:00', '2025-07-13 10:30', 'Chia sẻ về cơ hội và thách thức trong phát triển thị trường thực phẩm hữu cơ tại Việt Nam'),
(25, 15, 'Chiến lược phát triển thị trường hữu cơ', '2025-07-13 10:30', '2025-07-13 13:00', 'Bàn luận về chiến lược phát triển và mở rộng thị trường thực phẩm hữu cơ tại Việt Nam và thế giới');

ALTER TABLE seminars
ADD COLUMN photo VARCHAR(255);
ALTER TABLE seminars
ADD COLUMN photo VARCHAR(255) DEFAULT 'default-seminar.jpg';
UPDATE seminars SET photo = 'seminar1.jpg' WHERE seminar_id = 1;
UPDATE seminars SET photo = 'seminar2.jpg' WHERE seminar_id = 2;
UPDATE seminars SET photo = 'seminar3.jpg' WHERE seminar_id = 3;
UPDATE seminars SET photo = 'seminar4.jpg' WHERE seminar_id = 4;
UPDATE seminars SET photo = 'seminar5.jpg' WHERE seminar_id = 5;
UPDATE seminars SET photo = 'seminar6.jpg' WHERE seminar_id = 6;
UPDATE seminars SET photo = 'seminar7.jpg' WHERE seminar_id = 7;
UPDATE seminars SET photo = 'seminar8.jpg' WHERE seminar_id = 8;
UPDATE seminars SET photo = 'seminar9.jpg' WHERE seminar_id = 9;
UPDATE seminars SET photo = 'seminar10.jpg' WHERE seminar_id = 10;
UPDATE seminars SET photo = 'seminar11.jpg' WHERE seminar_id = 11;
UPDATE seminars SET photo = 'seminar12.jpg' WHERE seminar_id = 12;
UPDATE seminars SET photo = 'seminar13.jpg' WHERE seminar_id = 13;
UPDATE seminars SET photo = 'seminar14.jpg' WHERE seminar_id = 14;
UPDATE seminars SET photo = 'seminar15.jpg' WHERE seminar_id = 15;
UPDATE seminars SET photo = 'seminar16.jpg' WHERE seminar_id = 16;
UPDATE seminars SET photo = 'seminar17.jpg' WHERE seminar_id = 17;
UPDATE seminars SET photo = 'seminar18.jpg' WHERE seminar_id = 18;
UPDATE seminars SET photo = 'seminar19.jpg' WHERE seminar_id = 19;
UPDATE seminars SET photo = 'seminar20.jpg' WHERE seminar_id = 20;
UPDATE seminars SET photo = 'seminar21.jpg' WHERE seminar_id = 21;
UPDATE seminars SET photo = 'seminar22.jpg' WHERE seminar_id = 22;
UPDATE seminars SET photo = 'seminar23.jpg' WHERE seminar_id = 23;
UPDATE seminars SET photo = 'seminar24.jpg' WHERE seminar_id = 24;
UPDATE seminars SET photo = 'seminar25.jpg' WHERE seminar_id = 25;

UPDATE speakers SET photo = 'speaker1.jpg' WHERE speaker_id = 1;
UPDATE speakers SET photo = 'speaker2.jpg' WHERE speaker_id = 2;
UPDATE speakers SET photo = 'speaker3.jpg' WHERE speaker_id = 3;
UPDATE speakers SET photo = 'speaker4.jpg' WHERE speaker_id = 4;
UPDATE speakers SET photo = 'speaker5.jpg' WHERE speaker_id = 5;
UPDATE speakers SET photo = 'speaker6.jpg' WHERE speaker_id = 6;
UPDATE speakers SET photo = 'speaker7.jpg' WHERE speaker_id = 7;
UPDATE speakers SET photo = 'speaker8.jpg' WHERE speaker_id = 8;
UPDATE speakers SET photo = 'speaker9.jpg' WHERE speaker_id = 9;
UPDATE speakers SET photo = 'speaker10.jpg' WHERE speaker_id = 10;
UPDATE speakers SET photo = 'speaker11.jpg' WHERE speaker_id = 11;
UPDATE speakers SET photo = 'speaker12.jpg' WHERE speaker_id = 12;
UPDATE speakers SET photo = 'speaker13.jpg' WHERE speaker_id = 13;
UPDATE speakers SET photo = 'speaker14.jpg' WHERE speaker_id = 14;
UPDATE speakers SET photo = 'speaker15.jpg' WHERE speaker_id = 15;
UPDATE speakers SET photo = 'speaker16.jpg' WHERE speaker_id = 16;
UPDATE speakers SET photo = 'speaker17.jpg' WHERE speaker_id = 17;
UPDATE speakers SET photo = 'speaker18.jpg' WHERE speaker_id = 18;
UPDATE speakers SET photo = 'speaker19.jpg' WHERE speaker_id = 19;
UPDATE speakers SET photo = 'speaker20.jpg' WHERE speaker_id = 20;

UPDATE locations SET photo = 'location1.jpg' WHERE location_id = 1;
UPDATE locations SET photo = 'location2.jpg' WHERE location_id = 2;
UPDATE locations SET photo = 'location3.jpg' WHERE location_id = 3;
UPDATE locations SET photo = 'location4.jpg' WHERE location_id = 4;
UPDATE locations SET photo = 'location5.jpg' WHERE location_id = 5;
UPDATE locations SET photo = 'location6.jpg' WHERE location_id = 6;
UPDATE locations SET photo = 'location7.jpg' WHERE location_id = 7;
UPDATE locations SET photo = 'location8.jpg' WHERE location_id = 8;
UPDATE locations SET photo = 'location9.jpg' WHERE location_id = 9;


CREATE TABLE contact (
    contact_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(250) NOT NULL,
    email VARCHAR(250) NOT NULL,
    topic VARCHAR(250) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);