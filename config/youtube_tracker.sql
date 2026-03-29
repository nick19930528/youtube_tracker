-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- 主機： localhost
-- 產生時間： 2025 年 07 月 01 日 14:57
-- 伺服器版本： 10.3.7-MariaDB
-- PHP 版本： 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `youtube_tracker`
--

-- --------------------------------------------------------

--
-- 資料表結構 `channels`
--

CREATE TABLE `channels` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subscribed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscriber_count` int(11) DEFAULT 0,
  `video_count` int(11) DEFAULT 0,
  `thumbnail_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `channels`
--

INSERT INTO `channels` (`id`, `name`, `url`, `channel_id`, `note`, `added_at`, `subscribed_at`, `description`, `subscriber_count`, `video_count`, `thumbnail_url`, `published_at`, `category_id`) VALUES
(1, '1K圖解', 'https://www.youtube.com/channel/UC0JmsGA2RoO9YXH3bD2h18w', 'UC0JmsGA2RoO9YXH3bD2h18w', NULL, '2025-06-18 11:59:37', '2025-06-18 19:59:37', '媒體人，飛行員，說書人，我的兩便士\n\n全網僅油管一處，墻内皆爲盜版\n\n商務合作：ck110new@gmail.com\n', 87200, 66, 'https://yt3.ggpht.com/86D29QT7WjyXHUPLWu0_LHQhttiwx4Fi40fft5OCuEY_RLVqhYkfKpEnMM_ycSWkwYkVSh7njos=s88-c-k-c0x00ffffff-no-rj', '2008-01-29 06:46:36', 3),
(4, '7Car小七車觀點', 'https://www.youtube.com/channel/UCG_gH6S-2ZUOtEw27uIS_QA', 'UCG_gH6S-2ZUOtEw27uIS_QA', NULL, '2025-06-18 12:18:31', '2025-06-18 20:18:31', '《7Car 小七車觀點》每日提供最迅速、詳實的汽機車資訊，於 2007 年成立於奇摩部落格， 2008 年入選奇摩摩人，成為全台最大汽機車部落格，並於 2013 年正式轉型為汽機車媒體。\n每日提供閱聽眾最新、最多元的汽機車資訊，目前旗下設立《7Car 小七車觀點》、《Truck.tw 商車王》、《Auto Future 車未來》三大網站，YouTube、Facebook、Instagram、TikTok...等社群平台均有官方帳號，資訊內容亦授權 Google 新聞、Line today、Nownews、PC home 新聞、民視新聞網...等各大綜合性媒體平台傳遞。\n於新車資訊外，更提供全球各地的車輛產業、車輛文化及車輛娛樂...等多元資訊，期望以全球化、多元性的豐富題材，為提昇台灣車輛文化略盡棉薄之力。\n\n創辦人暨主持人：曾彥豪 〈小七〉\n私立亞東技術學院機械科汽車組畢業、國立台北科技大學車輛工程系學士、碩士。\n乙級汽車修護技術士、乙級汽車修護技工執照。\n1995 年起投身車輛產業，歷任修護技工、業代、汽車雜誌編輯、車廠品牌行銷專員、車廠行銷/銷售主管、各品牌訓練講師，具豐富產業實戰經驗。\n自幼熱愛汽車，收藏各式老車近百輛、汽車書籍、型錄、雜誌、文獻超過餘萬件，為台灣知名老車收藏家與車輛文化推廣者。\n2013 年將《7car 小七車觀點》正式改版為汽機車媒體，目前經營《7car 小七車觀點》、《Truck.tw 商車王》、《Auto Future 車未來》及其它業外事業，並為各大電視新聞台、汽車節目、廣播節目邀訪來賓。\n\n', 267000, 3886, 'https://yt3.ggpht.com/ytc/AIdro_ndVSTt24ctwqFW7zr4fY0R3xDrFpZRs4Ut5_-mdzTuZeU=s88-c-k-c0x00ffffff-no-rj', '2013-10-30 13:19:16', 11),
(5, '大耳朵TV', 'https://www.youtube.com/channel/UCD_cg9Tak9SvlPHRsWxUIpA', 'UCD_cg9Tak9SvlPHRsWxUIpA', NULL, '2025-06-18 14:20:46', '2025-06-18 22:20:46', '坐標韓國，主要分享數碼產品開箱及評測、韓國美食，以及日常Vlog視頻，請订阅我的頻道！\n聯繫郵箱st.nam0115@gmail.com', 359000, 837, 'https://yt3.ggpht.com/ytc/AIdro_l4LXYOnJTKQC98hDEsdLQ1V6uiCUTP9zbQN5fPbJCa_do=s88-c-k-c0x00ffffff-no-rj', '2017-05-18 10:05:20', 10),
(6, '豪 - 中华镖师', 'https://www.youtube.com/channel/UCpDPtV7W3SYuY3Tplv-elmQ', 'UCpDPtV7W3SYuY3Tplv-elmQ', NULL, '2025-06-20 02:12:19', '2025-06-20 10:12:19', '', 7170, 6, 'https://yt3.ggpht.com/zAEkkmAnhokjlTS8cM5AWVNNE1vVI-ZBXG1bW4DRztnUxK7CXvXQrMX_x1WmFnBTJ8VGuDJl=s88-c-k-c0x00ffffff-no-rj', '2025-05-12 08:22:01', 13),
(7, '老孫聊遊戲', 'https://www.youtube.com/channel/UCKPflKAE2Y1tm8VSi32iboQ', 'UCKPflKAE2Y1tm8VSi32iboQ', NULL, '2025-06-21 03:00:42', '2025-06-21 11:00:42', '天下玩友是一家:）商務合作或建议suntuwei@gmail.com（或116336740@qq.com）\nline 116336740\n', 455000, 824, 'https://yt3.ggpht.com/l8Llu-BHs0U8ff6gKgxZshwPbX8eklWkPS0FeFEFbOq5bneqdezM4sYMT_GPp4Txn6t3_4uvZxs=s88-c-k-c0x00ffffff-no-rj', '2012-09-03 21:56:27', 10),
(9, '维森来了Wilson', 'https://www.youtube.com/channel/UCQDixlPvtUhNbllbIPv-hfA', 'UCQDixlPvtUhNbllbIPv-hfA', NULL, '2025-06-21 03:01:26', '2025-06-21 11:01:26', '大家好，我是维森，这是我在YouTube的唯一账号\n我想寻找世界的真相\n分享旅途中的见闻\nHi everyone, I\'m Wilson.\nThis is my only YouTube channel. \nJoin me as I explore the truth about the world \nand share the insights and experiences I gather on this journey\n', 53700, 48, 'https://yt3.ggpht.com/fCa87JWhoXhbVmc2imJnsUYW15DP9CdeGk3T_EFjOA9UWKBiZJV_lHgqGWgspHfXpiGcUSzDGNM=s88-c-k-c0x00ffffff-no-rj', '2015-07-26 20:12:05', 4),
(14, '十三要和拳头', 'https://www.youtube.com/@shisanyao', 'UCduz2zplHaYCAtiaEIcxErw', NULL, '2025-06-21 03:43:15', '2025-06-21 11:43:15', '这是“十三要和拳头”在YouTube的唯一频道！\n欢迎订阅！旅行VLOG持续更新中！\n商业合作： 542514807@qq.com【备注十三要+品牌信息】\n\n', 183000, 366, 'https://yt3.ggpht.com/Cx2k37xBJgvRo30_uX_dK45uNdwbO6K-pI_Pva9_iHXnCVqygaYkyf0xz5ZfcBMV-rE_tC0Dqw=s88-c-k-c0x00ffffff-no-rj', '2021-01-22 11:13:37', 14),
(15, '【CMoney理財寶】官方頻道', 'https://www.youtube.com/channel/UCceGk_IAmSygU-RYUmC_EOw/videos', 'UCceGk_IAmSygU-RYUmC_EOw', NULL, '2025-06-21 03:56:27', '2025-06-21 11:56:27', 'CMoney致力於提供實用的投資理財資訊，幫助你做出更好的財務決策，從而創造更豐富的人生。無論你是投資新手還是有經驗的投資者，我們的內容將涵蓋專業投資、生活消費和理財教育，幫助每個人做好人生投資，提升財務素養，實現財務自由。\n\n👉現在就訂閱【CMoney理財寶】官方頻道、開啟小鈴鐺，掌握最新的投資理財趨勢，成為更聰明的投資者！\n\n【精彩節目】\n🔥《ETF錢滾錢》主持人：葉芷娟　隔週三晚上6:00\n🔥《股市錢滾錢》主持人：葉芷娟　隔週三晚上6:00\n🔥《房市最錢線》主持人：劉涵竹　每週四晚上6:00 \n🔥《理財資優生》主持人：張國蓮　隔週一晚上6:00\n🔥《人生投資學》、《同學！投資行不行》 不定期推出！\n\n《ETF錢滾錢》最全面的ETF解讀，幫你資產滾出大雪球！\n《股市錢滾錢》深入解析股市動向，多空都能賺！\n《房市最錢線》房產資訊不漏接，掌握自住投資最佳契機！\n《理財資優生》聽專家高手暢聊投資背後的思考與邏輯，一起晉升理財優等生！\n《人生投資學》投資達人背後的故事，啟發你的人生與投資！\n《同學！投資行不行》網友問答疑問一把抓，買賣訊號大解析！\n\n', 357000, 1806, 'https://yt3.ggpht.com/HExsxUypE0vzAPEjPR7fjzUwEl8tb46-Ntn8Vl_6WJbsUjc9km-4lZVyGlDvDJN5JXW9QumMSw=s88-c-k-c0x00ffffff-no-rj', '2012-10-04 16:41:01', 15),
(16, '【Aha Videos】', 'https://www.youtube.com/channel/UCYXiFjyLvFmEGgKiOp7lqbA/videos', 'UCYXiFjyLvFmEGgKiOp7lqbA', NULL, '2025-06-21 03:57:21', '2025-06-21 11:57:21', '有浓度、有深度、反映当代现实情绪的原创非虚构短视频品牌。\n\n【Aha Videos】as an original documentary video channel brand will reflect people’s emotions in contemporary reality with deeply views and human interest.', 107000, 164, 'https://yt3.ggpht.com/ytc/AIdro_mQ8w5ekJ3LJ8shxIhplVOku15D6GyHvd7GIDlUX0Fv0w=s88-c-k-c0x00ffffff-no-rj', '2017-12-13 17:57:57', 3),
(17, ' 史考特 Walking Wild', 'https://www.youtube.com/channel/UChkvwGyHIFUZmW35z0NEsUw/videos', 'UChkvwGyHIFUZmW35z0NEsUw', NULL, '2025-06-21 03:58:19', '2025-06-21 11:58:19', '\"希望透過影片讓大家看到各個角落值得分享的人, 動物, 與故事\" - Scott', 61000, 35, 'https://yt3.ggpht.com/iDpbTv4_RZBlKY5IfuGEoVOUALQGFLWWbls-UYGqu_dct0EpNRPai-5XWlx3BXDnqUv2cp-z3CI=s88-c-k-c0x00ffffff-no-rj', '2020-05-16 14:46:09', 3),
(18, '李船長筆記', 'https://www.youtube.com/channel/UCUCycRb1bBBVOj-x96EfMNQ/videos', 'UCUCycRb1bBBVOj-x96EfMNQ', NULL, '2025-06-21 03:59:10', '2025-06-21 11:59:10', '高级船长职称，一个真正的远洋船长，驾驶世界最大集装箱船，带你环游世界，探索神秘的海洋，科普航海知识，真实展现当代海员实际工作与生活\nI am the captain of a large container ship. I have been a captain for 16 years. I love the sea and ships. I really want to share what I see with you here. My videos are all shot by myself.\n', 249000, 615, 'https://yt3.ggpht.com/ytc/AIdro_nOdIPW3l8lv2KEEBDDM2zmCf0EbjAG3h4XvQdAopbveA=s88-c-k-c0x00ffffff-no-rj', '2020-01-02 21:48:10', 16),
(19, '澤北SG', 'https://www.youtube.com/channel/UC7A3Mbc2g61ccaOg6kvSw7A', 'UC7A3Mbc2g61ccaOg6kvSw7A', NULL, '2025-06-21 04:02:45', '2025-06-21 12:02:45', '即使只是個坐板凳的吊車尾，為了籃球也要全力向前衝啊！\n無論打球、評球還是寫球都非專業人士，一切只為熱愛。\nAll For Basketball, Love And Peace.\n—————————\n目前除了YouTube沒有其他平台帳號\n\n商務合作：hzynbusiness@outlook.com\n', 168000, 801, 'https://yt3.ggpht.com/Zq76LrrgmqLIZ3fnMrIDc9ROuzF0IDsCktUFiyq3SlP5w3snu9eU7KEqcW38shYzK6jZFN1Cjw=s88-c-k-c0x00ffffff-no-rj', '2022-09-07 10:14:23', 5),
(20, '10N觀點', 'https://www.youtube.com/channel/UCpr8r7y1k8Cr3jYFGOTh5lg/videos', 'UCpr8r7y1k8Cr3jYFGOTh5lg', NULL, '2025-06-21 04:03:00', '2025-06-21 12:03:00', '嘿！我是10N，熱愛體育、熱愛籃球。\n\n愛聽故事，\n愛說故事，\n愛寫故事。\n\nDon\'t give up speaking out of turn, once gave up, the game is over.', 132000, 217, 'https://yt3.ggpht.com/ytc/AIdro_ncmgzTsdgQsOOYnT2hCE3YLDkHlP3jRbUt2bmOTR1O-w=s88-c-k-c0x00ffffff-no-rj', '2019-05-28 10:35:16', 5),
(21, '11区小豪的故事', 'https://www.youtube.com/channel/UCk5yPS6FfbJjeaY-PwczPvw/videos', 'UCk5yPS6FfbJjeaY-PwczPvw', NULL, '2025-06-21 04:07:12', '2025-06-21 12:07:12', '日本一住就是12年啊！ 平时主要卖房子 然后在拍拍vlog、周五更新（基本）！日本房产经纪人呢！', 100000, 288, 'https://yt3.ggpht.com/ytc/AIdro_m4KNuAS0UmFuqrwL9FcXw7gg-VYFfOiVZqXOYd__zb20U=s88-c-k-c0x00ffffff-no-rj', '2014-08-13 00:01:50', 17),
(22, '20岁了还没去过星巴克', 'https://www.youtube.com/channel/UCsdLbTwziL6Tg97swkuThSg/videos', 'UCsdLbTwziL6Tg97swkuThSg', NULL, '2025-06-21 04:08:05', '2025-06-21 12:08:05', '联系方式：sandorclegane@yeah.net\nB站：https://space.bilibili.com/4249401\n日常在微博：二十岁了还没有去过星巴克', 185000, 284, 'https://yt3.ggpht.com/ytc/AIdro_kIWvGHlvPnI76f44ozyMBtYG31iHGKqmE7wp_gBQ9im5U=s88-c-k-c0x00ffffff-no-rj', '2013-08-14 17:07:07', 18),
(23, '30而立', 'https://www.youtube.com/channel/UC9198FdDrCV25H7XJblzmZw/videos', 'UC9198FdDrCV25H7XJblzmZw', NULL, '2025-06-21 04:09:06', '2025-06-21 12:09:06', 'YouTube，借我平台紀錄一下\n\n合作邀約信箱\n/\n30afraid@gmail.com', 26400, 102, 'https://yt3.ggpht.com/kEzy390_GSpB01eb5jhHeSuBjtsLrNtOqwgX8MPlq5ZNPZ_mQ7_yCDV2k4FksKoWoT3Ugnpp=s88-c-k-c0x00ffffff-no-rj', '2018-02-27 15:37:39', 2),
(24, 'Dumpling Soda', 'https://www.youtube.com/channel/UCI0i7CaDK2KVuwLE1zYhuTQ', 'UCI0i7CaDK2KVuwLE1zYhuTQ', NULL, '2025-06-22 00:32:25', '2025-06-22 08:32:25', 'Hihi👋 \n\n合作邀約，請直接聯繫：Creator Studio\nEmail: business@creatorstudioco.com\n', 1000000, 441, 'https://yt3.ggpht.com/l_OGpu0bv5AgsDJf-lxLY-p8-IXHQeV7hDypnCwJojCqjs8RNpsivvHQW8hM2e-uXr8-vDgJ3iw=s88-c-k-c0x00ffffff-no-rj', '2015-05-10 20:34:38', 19),
(25, 'Eric Stone Trucker', 'https://www.youtube.com/channel/UC91kNLCdrU2gZ0jQd9ANm8g', 'UC91kNLCdrU2gZ0jQd9ANm8g', NULL, '2025-06-22 00:32:46', '2025-06-22 08:32:46', 'Hi, I’m a long-haul truck driver from Taiwan, sharing my life on the road across Canada and the U.S.! On this channel, you’ll find insights into the daily life of a truck driver, stunning highway views, and tips about truck driving. My goal is to give you a glimpse into this profession and bring you along to experience North American trucking culture. Thank you for subscription!\n\n大家好，我是來自台灣的長途卡車司機，分享在加拿大和美國駕駛卡車的生活點滴！這裡可以看到卡車司機的日常、加拿大和美國的公路風景，還有卡車相關知識和經驗。希望能夠讓大家更了解這份工作，並且和我一起體驗北美的卡車文化，歡迎訂閱！\n\nInstagram: Eric_Stone_Trucker\nFacebook: Eric Stone Trucker\nEmail: ericjhang@icloud.com\n', 44100, 23, 'https://yt3.ggpht.com/GRljyxs1Qm_e1Lf2khqp_WHu33jdC2Expv_FbMjVobbTER-9gUL-OrCorvzZovLBsukt4vfV=s88-c-k-c0x00ffffff-no-rj', '2012-01-26 10:53:22', 13),
(26, '56BelowTV 零下56', 'https://www.youtube.com/channel/UCSX99Rvq5dszj6rzAPkdglA/videos', 'UCSX99Rvq5dszj6rzAPkdglA', NULL, '2025-06-22 00:42:08', '2025-06-22 08:42:08', '华人移民，百味人生!\n\n如果你或者你身边的人想分享自己的移民故事，请联系工作室邮箱：Talk@56Below.com\n\n———————————————————————————————————————————\n\n【零下56】网站：http://www.56Below.com\n\n如果你想找联系我们，除了可以扫码视频内二维码外，还可以直接添加以下联系方式：\n\n顾问Ada微信：Yukon56\n顾问Bella微信：Dongnan56 \n顾问Chloe微信：Vancouver56\n顾问Doris微信：Edmonton56\n顾问Eldora微信：Eldora56\n顾问Gerda微信：Gerda56\nTelegram： Dongnan56\nWhatsApp：+1 5879662656\nEmail: info@56Below.com \n\n欢迎订阅微信公众号：56Below\n', 499000, 937, 'https://yt3.ggpht.com/ytc/AIdro_lipWfr05o_6CSvTmpo-bfMwaNqNrUUfIA8tpCYmvXrVA=s88-c-k-c0x00ffffff-no-rj', '2019-12-27 13:53:04', 20),
(27, '70后慢生活', 'https://www.youtube.com/channel/UCTJgyolORtxo_xwePdggk4w/videos', 'UCTJgyolORtxo_xwePdggk4w', NULL, '2025-06-22 00:42:33', '2025-06-22 08:42:33', '大家好！我们是持MM2H签证，旅居马来西亚多年的上海家庭。\n女儿原在大马读国际高中，后衔接澳洲大学就读，现已回国就业。\n而我们因喜爱这个国家，所以目前仍在槟城与上海二地居住，享受候鸟生活！\n旅居大马有子女教育，生活成本，环境质量，社会风气等诸多方面的考虑。希望我能从一个中国家庭的角度把我们在大马，国内的真实生活体验分享给大家~\n分享生活是一种乐趣，感谢大家的支持和收看！\n另外，欢迎同时关注副频道：金宝宝 https://www.youtube.com/channel/UCyY_E8J0KAbOsyT0Xpnr0yQ\n邮箱地址：tuixiuriji70@163.com', 328000, 138, 'https://yt3.ggpht.com/ytc/AIdro_mxwg3C0N3idGiqTVvkiRs0K_RRzbu6kMIoMfBiTebSIw=s88-c-k-c0x00ffffff-no-rj', '2018-06-08 08:04:57', 19),
(28, '77子nanako旅遊', 'https://www.youtube.com/channel/UCCs97dsjgmN0XZpdYeAqwAg/videos', 'UCCs97dsjgmN0XZpdYeAqwAg', NULL, '2025-06-22 00:48:01', '2025-06-22 08:48:01', '不定時掉落影片♪\n\n｜聯絡我 contact me｜\n      Email: nanakoy1209@gmail.com\n      Instagram:https://instagram.com/77nanako_\n      Facebook:https://fb.me/77nanakolife\n      個人檔案:https://lit.link/77nanako\n\nIG:77nanako_ \nFB:77子nanako life\nYT:77子nanako旅遊、77子nanako生活\n', 61500, 76, 'https://yt3.ggpht.com/ytc/AIdro_mQZ6VG_6VvHyG_yeVkeo1ZKfC1zH7_SrA5YV-f8f1TeQ=s88-c-k-c0x00ffffff-no-rj', '2021-04-20 01:04:34', 21),
(29, '77老大', 'https://www.youtube.com/channel/UC8zkK0-g8S8Z_t8ZjUmpAlA/videos', 'UC8zkK0-g8S8Z_t8ZjUmpAlA', NULL, '2025-06-22 00:51:13', '2025-06-22 08:51:13', '77老大畢業於長庚中醫，目前沒有在執業，\n很多人問我為什麼不當醫生跑來拍影片，\n我知道醫生很賺錢、有地位、而且非常受人尊敬，\n但拍影片，也沒甚麼不好，\n醫生10分鐘，只能幫一個人，\n但我的影片10分鐘，可以幫助到很多人。\n只要心中有白袍，走到哪裡都是醫生！\n\n科學其實很好玩，\n醫學可以淺顯易懂，中醫也可以很時尚，\n藉由我的眼睛看世界，帶你用不同的角度看醫學、看人生。\n\n商業配合 77sevenboss@gmail.com\nIG: basil_77777   \nFacebook:77老大', 1690000, 742, 'https://yt3.ggpht.com/DB8VL4gKtkUx4W9vzjIEUggk4LyTWQR7fZcLLz4rr6wlGnM1zWPintTHGADu1dmmMNjAG6OjlA=s88-c-k-c0x00ffffff-no-rj', '2018-12-17 01:42:33', 22),
(30, '90後創業家掃地僧', 'https://www.youtube.com/channel/UCWMxmoBhchU3swFAciqagnw/videos', 'UCWMxmoBhchU3swFAciqagnw', NULL, '2025-06-22 01:37:26', '2025-06-22 09:37:26', '創業 賺錢 投資 理財 被動收入\n股票 期權 房地產 零成本致富\n\n我的頻道會分享以上內容的心法與實際技巧，希望幫助有緣的朋友達到財富自由。\n如果你對以上的內容感興趣，我的頻道將會是你不可錯過的選擇。\n\n創業之前，必須記著：\n我們這種人是孤獨的\n因為選擇創業的時候，就已經決定走上一條與眾不同的路\n\n道與術的配合，是我達致財富自由的關鍵\n多次失敗的經驗才換來無價的領悟。\n我很早就開始創業了，我知道如果想向上流動，只有創業這條路選擇\n窮人的選擇其實沒有太多，我不是出身在富裕的家庭，只能白手起家\n我從來沒問過家裡拿過一分錢，父母也沒有給我提供過什麼人脈\n在資源有限的情況下\n我只能摸著石頭，一步一腳印的走。\n\n在我20歲的時候，我開始創業，那時我還在讀大學\n就這樣，由普通的創業家，向著企業家的方向出發\n22歲大學畢業那年，公司已經自動化經營\n我有時間開始接觸股票投資，我花費大量的金錢去向那些專業的投資人請教\n我認為股票投資是一生的事情，金錢只是一種工具\n所以我對花這些錢沒有任何感覺\n每當我學一樣新的技能，我都會請市面上最的專業的人去指導\n而事實上，股票市場的確讓我穩定地獲得豐厚的回報\n\n我開始慢慢地對這個世界有了很深的領悟\n大道至簡，於是我把一切化繁為簡\n總結了富人該有的心法和技巧\n系統地每天堅持自己，反省自己\n\n在這裡，我希望把這一切分享給每位路過的有緣人\n我相信能夠讓我們相遇，定必是我們修來的緣分\n\n免費投資教學：https://sdsytschool.com/landing1699844398169\n\n本頻道內所有影片與文字內容嚴禁搬運至任何影音平台或網站\n如有發現將直接採取法律行動\n\n', 1020000, 353, 'https://yt3.ggpht.com/ytc/AIdro_nhwOS5QsMYNSx0dQ5WI1i42RBioy1unBrOe6-0MgaDjBg=s88-c-k-c0x00ffffff-no-rj', '2020-04-13 18:14:22', 15),
(31, '996創業家造雨俠', 'https://www.youtube.com/channel/UCq2ucV2Lo48AbJH_7tXVfCA/videos', 'UCq2ucV2Lo48AbJH_7tXVfCA', NULL, '2025-06-22 01:37:52', '2025-06-22 09:37:52', '大家好 我是造雨俠👋\n\n🐣因為我的家庭背景，在我大學時期就半工讀幫補家裡。從中我了解打工很難賺大錢，幫助我家庭脫貧，所以畢業後，我就一股衝勁，立馬創業。但因為缺乏市場經驗，沒有財商思維，沒有團隊，所以經營2年後失敗收場。\n\n😤但我痛思過去，積極學習，尋找方法，所以很有幸讓我找到的我生命中的貴人，我的企業導師。透過不斷學習與實戰，終於我成功華麗轉身，目前是三家企業董事長，及投資兩家民營企業。\n\n🥰 因為我遇見我的貴人，我成功翻身。所以我決定也要做別人的貴人，解救和我遇到一樣經歷的年輕創業家。我將會分享實戰的創業經歷，財商思維知識，以及更多成功企業家的故事。我希望現在的年輕人可以少走至少5年的冤枉路，盡早踏上成功的道路。\n\n若喜歡我，想和我一起學習，請訂閱我\n【996創業家造雨俠】 - https://www.youtube.com/channel/UCq2ucV2Lo48AbJH_7tXVfCA?sub_confirmation=1\n\n🔥頻道宗旨：傳授創業投資經驗 培養更多企業家和有錢人\n\n✅頻道更新：每周五晚上7pm，記得關注我們的頻道！\n\n❤️ 想要與世界各地的優秀青年一起學習進步嗎？\n⏬ 加入我們的【黃金人脈圈】頻道會員 ⏬⏬⏬:\n✅ https://www.youtube.com/channel/UCq2ucV2Lo48AbJH_7tXVfCA/join\n\n💖💖  打賞一杯咖啡，能讓我們團隊繼續為您製作更多優質內容 ☕☕\npaypal.me/996zaoyuxia\n', 545000, 300, 'https://yt3.ggpht.com/_J896_JZ6EiTunbn_Cdjy7Xc8Y-cl3qdAovnuF8y7DX7lZVRNyJ5hmw4ITObSY7GL6VD8MES-wg=s88-c-k-c0x00ffffff-no-rj', '2021-07-05 23:12:48', 15),
(33, 'A-YEON', 'https://www.youtube.com/channel/UCdld7SHk9IyYSkNrHA9B6Dw/videos', 'UCdld7SHk9IyYSkNrHA9B6Dw', NULL, '2025-06-22 01:38:58', '2025-06-22 09:38:58', '아연\n\n비즈니스 문의     ayeon.official.com@gmail.com ❤\n\n', 2360000, 81, 'https://yt3.ggpht.com/ytc/AIdro_lC_6meecKZKz_oNwoPbomoL_NhSk28dnWe_m0OUJjpkFk=s88-c-k-c0x00ffffff-no-rj', '2015-10-30 15:16:56', 9),
(34, '胖貓堂主', 'https://www.youtube.com/channel/UCHrgWMrjOVCo5VSYcVV8eMg', 'UCHrgWMrjOVCo5VSYcVV8eMg', NULL, '2025-06-22 10:00:22', '2025-06-22 18:00:22', '講述企業家創業、品牌、金融商業歷史領域的小故事。\n\n深入探索那些改變世界的企業家，揭示他們的創新思維和奮鬥歷程。您將在這裡瞭解到各種品牌創始人的傳奇故事，包括他們從無到有的創業旅程、面臨的挑戰以及最終取得的巨大成功。我將分享他們的智慧、洞察力和創業經驗，幫助您從他們的成功中獲得靈感和啓發。視頻涵蓋廣泛的行業和商業歷史事件，包括科技、零售、娛樂、食品和飲料等領域。\n\n如果您對商業歷史和創業感興趣，或者想瞭解那些改變世界的企業家們是如何實現他們的夢想的，我的頻道將成為您不可或缺的資源。訂閱我的頻道，與我一起踏上這段令人興奮的商業之旅！\n\n請記得點贊、留言和分享我的視頻。謝謝您的支持，期待與您一起探索商業世界的奇跡！\n', 23400, 54, 'https://yt3.ggpht.com/mlvux2iqeUnTcjXdbaOGaX9bmW4FVsbPxWgB50wQKQTRswl4n64iEkno_9YlcIFMjwJzagv9jw=s88-c-k-c0x00ffffff-no-rj', '2023-04-04 10:34:23', 23),
(35, 'AddMaker 加點製造', 'https://www.youtube.com/channel/UChX7XsPV8s_k0hFnIM9V3bw/videos', 'UChX7XsPV8s_k0hFnIM9V3bw', NULL, '2025-06-23 07:14:45', '2025-06-23 15:14:45', '我們關注設計、製造領域，用影片讓產業知識更有趣！\n合作請來信：info@addmaker.tw\n我們的板金課：\nhttps://events.addmaker.tw/courses/sheet-metal-production/\n\nAddMaker 設計製造交流平台\nhttps://addmaker.tw/topics\n\n深入產業的影像設計團隊：製造本事\nhttps://makereal.tw\n', 82700, 106, 'https://yt3.ggpht.com/GiVp066CpoDGjzCDZBQkfOCv-1ohphUbCge59uscM9uIe-2dzQZogOk1YAIXkouX4plE-fy__qs=s88-c-k-c0x00ffffff-no-rj', '2018-03-15 13:36:13', 24),
(36, 'Aden Films', 'https://www.youtube.com/channel/UCu9g5OmzcCpcJnmSYyHnIVw/videos', 'UCu9g5OmzcCpcJnmSYyHnIVw', NULL, '2025-06-23 07:16:58', '2025-06-23 15:16:58', 'Thanks for stopping by at Aden. This channel is all about Gourmet Food, Street Food and a bit of Dance and Music.\n\nNot interested in partnerships, sponsorships, merch sales, promotions, video licensing, networks, etc. \n\nGli chef Michelin si mettono in contatto per video per condividere ricette\n米其林厨师可以联系(我)\nミシュランのシェフがレシピを共有するためにビデオで連絡を取る\nContact via Instagram or Email: ronamull@gmail.com\n\nComments: Thanks for your comments below the videos. Disrespectful or inappropriate comments towards cooks or towards people in the video will be kindly deleted. Rude commenters (spammers, haters) will be kindly banned. Cooks can have the comment section below their video closed or their faces blurred if they dislike the comments.', 2330000, 856, 'https://yt3.ggpht.com/ytc/AIdro_mW7XptZkENb0ioh_oSF60CTX1y_Ftk9ZD-vY4I2LbJ-kE=s88-c-k-c0x00ffffff-no-rj', '2012-08-08 20:42:04', 25),
(38, '今日華夏', 'https://www.youtube.com/channel/UCi5pSD3iwKIENCFjkAaghQw', 'UCi5pSD3iwKIENCFjkAaghQw', NULL, '2025-06-26 05:10:51', '2025-06-26 13:10:51', '', 28500, 365, 'https://yt3.ggpht.com/o2p5_DDHK0lSpuCNBWJiXh4zIQjcSOp5cPjQDHYbToiFrxvOoTSRhv7yaHKRRWnF7HZu6iLW6hI=s88-c-k-c0x00ffffff-no-rj', '2022-08-20 08:14:43', 8),
(39, '白呀白Talk', 'https://www.youtube.com/channel/UCjEqklvNFQYeqUPEmLY30jg', 'UCjEqklvNFQYeqUPEmLY30jg', NULL, '2025-06-26 05:11:36', '2025-06-26 13:11:36', '我是老白，一名在深圳搬砖的80后BSP工程师。这是我的个人频道，聊一些我感兴趣的话题，专注分享有价值的科技内容。\n同时，我还会不定期的直播；每周写一篇技术博客。让我们一起学习，一同进步吧~！\n\nHi, I\'m W., an embedded BSP engineer, P.E. working in Shenzhen, China.\nI make videos about technology, science and some quick thoughts.\nI also write a weekly blog that contains some tech infos and links to interesting things.\nLet\'s try to make it easy and learn together~!\n\n\n', 115000, 244, 'https://yt3.ggpht.com/ytc/AIdro_miHTRSXQfpnhgPbQwEnRRPBBRbarKFZM62Dn6sdjQSnvI=s88-c-k-c0x00ffffff-no-rj', '2010-04-22 06:17:45', 1),
(40, 'Lue有引力', 'https://www.youtube.com/channel/UC9Q9cIwv798cyLXJ7LbRMqg', 'UC9Q9cIwv798cyLXJ7LbRMqg', NULL, '2025-06-26 05:11:58', '2025-06-26 13:11:58', '本频道所有视频均为原创作品，严禁转载。如有侵权，将追究其责任\n\n', 151000, 2153, 'https://yt3.ggpht.com/ytc/AIdro_lS80FHhcGE9SRTo0ss2UDS6ynIaxeZ3SHLwhJUwHmOeA=s88-c-k-c0x00ffffff-no-rj', '2021-03-01 10:08:06', 5),
(41, '隨意畫', 'https://www.youtube.com/channel/UC7Lk1qy5OExqg_7OnkHwXrg', 'UC7Lk1qy5OExqg_7OnkHwXrg', NULL, '2025-06-26 05:12:52', '2025-06-26 13:12:52', '大家可以叫我隨意哥\n是一名喜歡畫畫的3D藝術家\n希望能透過影片，分享不同的故事\n', 29300, 42, 'https://yt3.ggpht.com/OZZGkqWOrBlIg5l1OWgdzPa2ZXrOd54tmHs1VrfUDvEHEFGaoyhhI1r8SjHlIvFM8OOR7aUbgA=s88-c-k-c0x00ffffff-no-rj', '2012-08-15 10:55:00', 26),
(42, '西瓜吹雪', 'https://www.youtube.com/channel/UCfC1xnx1zdgHTiT9El7eYqQ', 'UCfC1xnx1zdgHTiT9El7eYqQ', NULL, '2025-06-26 05:13:11', '2025-06-26 13:13:11', 'Hello大家好！這裡是你西瓜吹雪！\n🚖美食解说员 🌭地摊游击队 开心一笑\n今天我就正式入駐YouTube啦！\n我是一個立志要帶上大家和我一起吃遍全世界的人！\n如果你喜歡我的頻道，喜歡我的視頻的話！\n前往不要忘記點擊訂閱哦！\n訂閱鏈接：https://reurl.cc/eDrv3K\n', 21900, 890, 'https://yt3.ggpht.com/STsnUwuH0YhTHznqm7tnng1Q9rn7jLECoa_uPosfiHKvehuAKXjCwfZ4AFZBbn8W9xq5YewN3Q=s88-c-k-c0x00ffffff-no-rj', '2023-02-09 15:06:46', 18),
(43, 'agalily', 'https://www.youtube.com/channel/UCyj5XdXObYEj4PUFU-iyqJA', 'UCyj5XdXObYEj4PUFU-iyqJA', NULL, '2025-06-26 05:14:59', '2025-06-26 13:14:59', ':)', 11100, 14, 'https://yt3.ggpht.com/EIlmepVchsM3n7WcbmXUBHV1nzViizZ0C1oT8f3Hk6Z28zmoPC-YR5TYMDldk0OpyxMzyABVSg=s88-c-k-c0x00ffffff-no-rj', '2020-05-25 15:45:56', 18),
(44, '阿杜游中国', 'https://www.youtube.com/channel/UCO8VQ7fQd7JOGXpYpIAdmmQ/videos', 'UCO8VQ7fQd7JOGXpYpIAdmmQ', NULL, '2025-06-26 05:15:21', '2025-06-26 13:15:21', '大家好，这里是阿杜游中国的YouTube 官方频道\n自驾旅行，希望能通过这个平台\n把自己看到的奇观异景、传统风俗\n城市发展、人文风情、展示给大家\n喜欢的话就点击个订阅吧！\n\nHello, everyone. This is the official YouTube channel of Adu You in China.\nSelf-driving travel, I hope to go through this platform.\nTake the wonders and traditional customs you see.\nUrban development, cultural customs, show to everyone\nIf you like it, click to subscribe!\n\n本频道制作的所有影片都符合平台的使用政策，没有任何违规行为。\n', 78000, 468, 'https://yt3.ggpht.com/NnCqDUgc6B25U8ziSTSY4dY14lP3LrgzfpHbnMkhqubqrh4141O9zrn7ctSa_WzggNd0Dy70Cw=s88-c-k-c0x00ffffff-no-rj', '2021-01-07 14:43:32', 18),
(45, 'AJ Lapray', 'https://www.youtube.com/channel/UChmoaIrhb1K0XU9Wpyv-z2Q/videos', 'UChmoaIrhb1K0XU9Wpyv-z2Q', NULL, '2025-06-26 05:15:55', '2025-06-26 13:15:55', 'Welcome to my channel! \n\nI played Division 1 basketball at The University of Oregon, Pepperdine University, & Rice University\n\nMy videos consist of all the things I love!\n\n6\'5\" kid from Salem, OR\n\nStay up to date by following:\nTwitter: https://twitter.com/AJLapray\nInstagram: https://www.instagram.com/ajaylapray/\nSnapchat: AngeloJayLapray\nFacebook: https://www.facebook.com/ajlapray', 741000, 470, 'https://yt3.ggpht.com/ytc/AIdro_miEA3MN44tc51XIg1AxVQAQsPNskMbhd-LnpzW7I0Bm4A=s88-c-k-c0x00ffffff-no-rj', '2011-10-07 13:01:10', 5),
(46, 'CD_katana', 'https://www.youtube.com/channel/UCMXTIWLHHH7tRV-N6xp0KcQ', 'UCMXTIWLHHH7tRV-N6xp0KcQ', NULL, '2025-06-27 07:15:56', '2025-06-27 15:15:56', '此号已停更，请朋友们关注我的油管新号“CD聊机车”，https://www.youtube.com/channel/UCa-61fiGEnpaexEmhI8HDfw\n', 6160, 31, 'https://yt3.ggpht.com/N34fuPlsUats_G-kq_ymDwN7xi9xkBg0tx796CnxTOu8_66ojZcugJVtBZtw2QnE2qmf3W1Kew=s88-c-k-c0x00ffffff-no-rj', '2021-08-23 15:22:17', 11),
(47, '小Lin说', 'https://www.youtube.com/channel/UCilwQlk62k1z7aUEZPOB6yw', 'UCilwQlk62k1z7aUEZPOB6yw', NULL, '2025-06-27 07:16:25', '2025-06-27 15:16:25', 'Hi~ 我是Lindsay，欢迎来到我的频道！希望能跟大家一起乐乐呵呵涨点知识~~\n简单介绍一下我自己：北大本科 - 哥大研究生 - JP Morgan- 创业\nB站、抖音、视频号、知乎、小红书、头条同名“小Lin说”\n\n！！没有Facebook，没有Telegram，没有Whatsapp，没有Tiktok，没有粉丝群，这些平台所有账号都是骗子！！\n不会以任何方式主动要求私信，请大家谨防受骗！！\n\n================加入我们================\n小Lin团队希望能找到喜爱我们频道的有识之士。如果对内容、视频制作、运营感兴趣的小伙伴欢迎把简历和自我介绍发送到：xiaolin_recruiting@163.com\n\n', 2490000, 133, 'https://yt3.ggpht.com/dDOuCyzkVBwGapJot3mAGqq1_2_sng7pgnPtkGF1uSmcleO4p6O4Ox6flFzwF7vYDiuNv2I_mA=s88-c-k-c0x00ffffff-no-rj', '2013-01-27 15:38:51', 15),
(48, '硅谷101', 'https://www.youtube.com/channel/UCKV2yWPB3wn0RTZh3cTD8YA', 'UCKV2yWPB3wn0RTZh3cTD8YA', NULL, '2025-06-29 12:00:15', '2025-06-29 20:00:15', '驶向未来。 \n一档深度科技与商业视频栏目，旗下有同名播客。通过对前沿科技及商业的一手视角和深度挖掘，我们希望带来深度有趣的前沿选题。Solid journalism still matters.\n联系我们：video@sv101.net \n', 266000, 106, 'https://yt3.ggpht.com/Vqg_8nFFDNbk_IvXWE7ngTBTTx3h1StP7nPtRQv77UVvtXfHurANruNVH3MA-Ms-OC-6Ge0L=s88-c-k-c0x00ffffff-no-rj', '2022-03-03 13:21:04', 1),
(49, '好奇羅盤', 'https://www.youtube.com/channel/UC1Vz1h2FNv7jrHS_Z9zu2jw', 'UC1Vz1h2FNv7jrHS_Z9zu2jw', NULL, '2025-06-29 12:01:24', '2025-06-29 20:01:24', '好奇心當作引擎，跟著羅盤一起，探索地理背後的趣聞～如果覺得影片還不錯，各位的訂閱就是我更新的最大動力！\n\n影片動畫製作比較繁複，難免會有小疏漏，資訊僅供參考，歡迎大家指正！\n\n注：影片部分知識輸出方式為二創，部份資訊及排版參考其他影片\n我們尊重原創者的權利，如有侵權請告知，感謝您的諒解與支持。\n', 123000, 144, 'https://yt3.ggpht.com/A7jssA8GAJHRUsBNPR05c26aCmHUO6fxwgo3avhNrY3A2Lcu174uC8uZmanLsCx5bfwNHsS_Wg=s88-c-k-c0x00ffffff-no-rj', '2023-07-01 18:59:02', 3),
(50, 'AJ食旅 Recvideo', 'https://www.youtube.com/channel/UC6NBC3xO4OnPn7y_bM1h-TQ', 'UC6NBC3xO4OnPn7y_bM1h-TQ', NULL, '2025-06-29 12:02:49', '2025-06-29 20:02:49', '', 44600, 168, 'https://yt3.ggpht.com/ytc/AIdro_nvvXB8DlKvHTZ7DQ6CMInKj9yJXtbOdx15EqUOMHfKPdI=s88-c-k-c0x00ffffff-no-rj', '2012-03-21 16:25:18', 25),
(51, 'Alexander Paiva', 'https://www.youtube.com/channel/UCpQdZfw7iktmK-DPZzaDzkA', 'UCpQdZfw7iktmK-DPZzaDzkA', NULL, '2025-06-29 12:04:09', '2025-06-29 20:04:09', 'Hello everyone! My names is Alex! I love to play drums and play along to my favorite songs! I cover mostly Rock and Metal but I have an open mind to check out other types of music as well and cover what ever song I really like!  If you like my drum covers, why not give a like and subscribe for more videos in the future! Thanks for watching! ', 152000, 87, 'https://yt3.ggpht.com/ytc/AIdro_m6gT04Wl6P7APmaGZ475IO-CcDSQDFHGFqfegKNj2EmbA=s88-c-k-c0x00ffffff-no-rj', '2009-03-11 12:04:24', 9),
(52, 'All process of world', 'https://www.youtube.com/channel/UCSNIT8Z40XgB4RKk9Vhf1eA', 'UCSNIT8Z40XgB4RKk9Vhf1eA', NULL, '2025-06-29 12:05:12', '2025-06-29 20:05:12', '\"We present the precious processes around us to you\"\n\n📧 Contact : allprocessofworld@gmail.com\n', 3150000, 721, 'https://yt3.ggpht.com/zAnMxGcisnqRL5pybh3xNWtbd2c-k5bhD8TVzqMEFL01RQBQp6DbtRIT-VlPCUAzVO1Dd0kIS2I=s88-c-k-c0x00ffffff-no-rj', '2020-03-20 21:49:36', 24),
(53, '电丸科技AK', 'https://www.youtube.com/channel/UCZVThl_MRppEdGUPGjXSSdg', 'UCZVThl_MRppEdGUPGjXSSdg', NULL, '2025-06-29 12:05:46', '2025-06-29 20:05:46', '聊聊泛科技的话题，包括不限于VR，通信，网络，游戏，摄影，计算机，芯片。几乎不开箱，不评测。\n', 457000, 405, 'https://yt3.ggpht.com/jW7UHGWhP7Qs_OQ49KsgRDl33ZmO6twiLHgUfDoCu9b2rtk6jnMSh3vEPH08wmf3BJcDzysQ=s88-c-k-c0x00ffffff-no-rj', '2006-10-24 12:21:11', 10),
(54, 'Ben Eater', 'https://www.youtube.com/channel/UCS0N5baNlQWJCUrhCEo8WlA', 'UCS0N5baNlQWJCUrhCEo8WlA', NULL, '2025-06-29 12:20:21', '2025-06-29 20:20:21', 'Subscribe to see tutorial-style videos about electronics, computer architecture, networking, and various other technical subjects. If you want to see more on a particular subject, leave a comment and I\'ll try my best to add more.\n\nIf you\'d like to support my work, please do!\nhttps://www.patreon.com/beneater\nhttps://paypal.me/beneater\nhttps://cash.me/$eater\nbitcoin:1EaterJkmmuJWfm8hvULrMJGm7R8JgXTL8\nbitcoincash:1JUjEErUjkgBVJP28GH6LKihvEUJ1RGZhx', 1310000, 126, 'https://yt3.ggpht.com/ytc/AIdro_kzQ9Fabth3QHyk1YLRHA62goVPgxJdd68G0CPIs0tU3A=s88-c-k-c0x00ffffff-no-rj', '2011-10-15 10:37:39', 1),
(55, 'The Retro Future', 'https://www.youtube.com/channel/UCefAbzsWZE4uXU-mqQMrr4Q', 'UCefAbzsWZE4uXU-mqQMrr4Q', NULL, '2025-06-29 12:36:02', '2025-06-29 20:36:02', 'Welcome to The Retro Future.\n\n\n', 613000, 610, 'https://yt3.ggpht.com/ZlMO8TmNIu0FJW5G1oLAqBGXdNJu3rmEn7AtRLslwG0xr45OxBWXzVjahX68uAVunlyl9_vX=s88-c-k-c0x00ffffff-no-rj', '2014-03-08 04:45:04', 1),
(56, '谈三圈', 'https://www.youtube.com/channel/UCOjm3MCxpq_YF4ywdnhZMkA', 'UCOjm3MCxpq_YF4ywdnhZMkA', NULL, '2025-06-30 06:09:46', '2025-06-30 14:09:46', '芯片大厂工程师｜ACE认证健身教练｜德国老司机｜ 萨克斯手\n全网同名—— 谈三圈\n', 40200, 41, 'https://yt3.ggpht.com/FuHcYgCx7s_jY040d3ax4YbWKuwgya-IxMS3olbuQYzNm8q5cGT36w_neUGYV4TM3kzCEr-uaA=s88-c-k-c0x00ffffff-no-rj', '2020-08-19 06:08:51', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `channel_categories`
--

CREATE TABLE `channel_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort_order` int(11) DEFAULT 99
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 傾印資料表的資料 `channel_categories`
--

INSERT INTO `channel_categories` (`id`, `name`, `sort_order`) VALUES
(1, '科技', 5),
(2, '生活', 1),
(3, '知識 冷', 3),
(4, '旅遊', 2),
(5, '籃球', 10),
(6, '娛樂 威士忌 調酒', 99),
(7, '健身', 99),
(8, '新聞', 99),
(9, '音樂', 99),
(10, '3C', 5),
(11, '汽車', 10),
(13, '生活 卡車', 1),
(14, '生活 房車', 1),
(15, '理財', 4),
(16, '生活 貨輪', 1),
(17, '房產 日本', 10),
(18, '生活 中國', 1),
(19, '生活 馬來西亞', 1),
(20, '移民', 10),
(21, '旅遊 日本', 2),
(22, '知識 醫學', 3),
(23, '創業', 4),
(24, '製造', 5),
(25, '料理', 99),
(26, '動畫', 99);

-- --------------------------------------------------------

--
-- 資料表結構 `search_history`
--

CREATE TABLE `search_history` (
  `id` int(11) NOT NULL,
  `keyword` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `searched_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `videos`
--

CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `youtube_url` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `is_watched` tinyint(1) DEFAULT 0,
  `watched_at` datetime DEFAULT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  `published_at` datetime DEFAULT NULL,
  `view_count` int(11) DEFAULT 0,
  `like_count` int(11) DEFAULT 0,
  `comment_count` int(11) DEFAULT 0,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `channel_name` varchar(255) DEFAULT NULL,
  `duration` varchar(20) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 傾印資料表的資料 `videos`
--

INSERT INTO `videos` (`id`, `title`, `youtube_url`, `summary`, `is_watched`, `watched_at`, `added_at`, `published_at`, `view_count`, `like_count`, `comment_count`, `thumbnail_url`, `channel_name`, `duration`, `location`) VALUES
(27, '旷野的召唤，从四川到新疆，十天跨越3000km，大件运输全过程 #超级工程制造 #中国风光', 'https://www.youtube.com/watch?v=sAu-ntl9rbk', '這段影片記錄了從四川到新疆的旷野之旅，十天跨越3000公里，展示了大件運輸的全過程。這是一個關於超級工程制造和中國風光的故事。', 1, NULL, '2025-06-20 10:27:14', '2025-05-13 19:33:12', 86216, 1459, 149, 'https://i.ytimg.com/vi/sAu-ntl9rbk/default.jpg', '豪 - 中华镖师', 'PT13M59S', NULL),
(30, '我来到孟加拉富人区 高墙隔绝 随处是安保人员 维森来了', 'https://www.youtube.com/watch?v=2Izu0pzITwU', '影片主要描述作者來到孟加拉富人區，這裡高牆隔絕且隨處可見安保人員，並介紹了一位名為維森的人物。', 1, NULL, '2025-06-21 10:59:07', '2025-06-20 18:00:04', 9199, 256, 16, 'https://i.ytimg.com/vi/2Izu0pzITwU/default.jpg', '维森来了Wilson', 'PT10M45S', NULL),
(31, '開了22年的數碼店，終於搬遷完成了！', 'https://www.youtube.com/watch?v=h_v-T09RO9o', '這段影片講述一間已經經營了22年的數碼店，終於搬遷完成的故事。', 1, NULL, '2025-06-21 10:59:16', '2022-06-23 19:00:02', 551046, 16975, 1637, 'https://i.ytimg.com/vi/h_v-T09RO9o/default.jpg', '老孫聊遊戲', 'PT12M57S', NULL),
(32, '到中国烤肉之都旅游！东北人吃烤肉这么野？这里就是加大版的世界！', 'https://www.youtube.com/watch?v=BrkkMbkr0qM', '這段影片介紹了前往中國烤肉之都旅遊的經歷，展示了東北人食用烤肉的方式和風格，並形容這裡就像是世界的加大版。', 1, NULL, '2025-06-21 10:59:20', '2025-06-20 16:00:11', 10326, 416, 17, 'https://i.ytimg.com/vi/BrkkMbkr0qM/default.jpg', '十三要和拳头', 'PT10M37S', NULL),
(79, '【免費】我自己做了一款APP！iOS 26這樣設置超炸｜大耳朵TV', 'https://www.youtube.com/watch?v=hZLi_0P7gfQ', '', 1, '2025-06-24 17:44:03', '2025-06-21 11:43:40', '2025-06-20 17:20:13', 23470, 754, 84, 'https://i.ytimg.com/vi/hZLi_0P7gfQ/default.jpg', '大耳朵TV', '444', NULL),
(98, '一口气了解稳定币是咋回事？', 'https://www.youtube.com/watch?v=Z-LpKv5QsWE', '這段影片將解釋什麼是穩定幣。', 1, '2025-06-21 12:39:28', '2025-06-21 12:39:28', '2025-05-30 12:10:25', 96933, 2162, 109, 'https://i.ytimg.com/vi/Z-LpKv5QsWE/default.jpg', '苏涵Susuhan', 'PT7M10S', NULL),
(101, '拒絕美國回歸台灣！年僅19歲的天才後衛賀博是否有機會成為下一個林書豪？已經成為台北隊內得分王、打法完全適應NBA，或將參加2026年選秀｜澤北SG', 'https://www.youtube.com/watch?v=911ksM_TETU', '', 1, '2025-06-21 20:58:49', '2025-06-21 20:55:46', '2025-06-21 19:00:17', 7453, 310, 44, 'https://i.ytimg.com/vi/911ksM_TETU/default.jpg', '澤北SG', '606', NULL),
(103, '女人一樣開大船！台北女引水指揮巨輪“蛇形走位”，吹攏風離碼頭！', 'https://www.youtube.com/watch?v=KyHbfySWQxQ', '', 1, '2025-06-24 17:43:38', '2025-06-21 20:55:46', '2025-06-20 19:00:41', 74718, 2762, 178, 'https://i.ytimg.com/vi/KyHbfySWQxQ/default.jpg', '李船長筆記', '890', NULL),
(105, '【新車試駕】2025 Kia EV6 增程版 ｜科技再升級，改款再進化！【7Car小七車觀點】', 'https://www.youtube.com/watch?v=Iy80qYFBnzA', '', 1, '2025-06-21 20:58:57', '2025-06-21 20:55:51', '2025-06-20 18:00:25', 2478, 51, 8, 'https://i.ytimg.com/vi/Iy80qYFBnzA/default.jpg', '7Car小七車觀點', '1233', NULL),
(108, '開箱4｜美軍裝備竟然能買？開箱美國超狂倉庫｜US Army Surplus Store Real Tour', 'https://www.youtube.com/watch?v=tQGDz0-2OLo', '這段影片主要內容是展示主持人開箱美國超狂倉庫中的美軍裝備，讓觀眾了解原來美軍裝備也能在市場上買到。', 1, '2025-06-22 08:30:30', '2025-06-22 08:30:30', '2025-06-20 18:01:40', 64823, 2071, 282, 'https://i.ytimg.com/vi/tQGDz0-2OLo/default.jpg', 'Eric Stone Trucker', '970', NULL),
(109, '最GG的商務艙？日本航空JAL帶我穿越回80年代😭', 'https://www.youtube.com/watch?v=IlZ2_PgGY3w', '這段影片探討日本航空JAL的商務艙服務，讓人感覺回到了80年代。', 1, '2025-06-22 08:31:25', '2025-06-22 08:31:25', '2025-06-21 15:42:26', 27225, 366, 15, 'https://i.ytimg.com/vi/IlZ2_PgGY3w/default.jpg', 'Dumpling Soda', '566', NULL),
(112, '你喝的味全果汁背后竟有这样的故事！从水果小贩到台湾食品到建立起台湾食品帝国', 'https://www.youtube.com/watch?v=EVaJOsdPU4M', '這段影片探討了味全果汁背後的故事，從水果小販開始，到建立起台灣食品帝國的過程。', 1, '2025-06-22 18:01:33', '2025-06-22 18:01:33', '2025-06-20 13:38:34', 123853, 1398, 206, 'https://i.ytimg.com/vi/EVaJOsdPU4M/default.jpg', '胖貓堂主', '1035', NULL),
(115, '【突發】1個月後全球通脹大蕭條？美國自導自演的石油危機，是財富大洗牌的暴富機會？現在發生的事跟1929年一模一樣，黃金石油匯率關稅都是局？資本家早就佈好局等全世界接盤？', 'https://www.youtube.com/watch?v=kHuUkN6h35A', '', 0, NULL, '2025-06-23 10:11:42', '2025-06-22 20:30:59', 186659, 6386, 378, 'https://i.ytimg.com/vi/kHuUkN6h35A/default.jpg', '90後創業家掃地僧', '1717', NULL),
(118, '朝鮮驚現巨型地畫 箭頭直指...今天視頻可能會被下架 | 1K圖解', 'https://www.youtube.com/watch?v=lHX3Svwii5A', '', 1, '2025-06-23 17:23:11', '2025-06-23 10:11:52', '2025-06-22 20:10:39', 16892, 701, 61, 'https://i.ytimg.com/vi/lHX3Svwii5A/default.jpg', '1K圖解', '1324', NULL),
(119, '一只风力叶片，从内蒙古到西藏，全程3800km平均海拔4500米，运往世界屋脊的惊险全过程！！！#超级工程制造 #中国风光 #东方电气 #重器山河行', 'https://www.youtube.com/watch?v=44YCBqGw2N0&t=236s', '这段影片展示了一只风力叶片从内蒙古到西藏全程3800km，平均海拔4500米的运输过程，向世界屋脊运送的惊险全过程。视频中展示了超级工程制造、中国风光、东方电气和重器山河行的特点。', 1, '2025-06-23 15:11:35', '2025-06-23 15:11:35', '2025-05-15 19:38:27', 210009, 3085, 855, 'https://i.ytimg.com/vi/44YCBqGw2N0/default.jpg', '豪 - 中华镖师', '914', NULL),
(120, '地下商業街一片漆黑，我送修的XBOX還能拿回來嗎', 'https://www.youtube.com/watch?v=XkfP57rX2Mc&t=697s', '這段影片描述地下商業街突然停電，主角擔心自己送修的XBOX是否能順利拿回來。', 1, '2025-06-23 15:11:42', '2025-06-23 15:11:42', '2025-01-10 19:26:37', 399869, 6430, 569, 'https://i.ytimg.com/vi/XkfP57rX2Mc/default.jpg', '老孫聊遊戲', '932', NULL),
(121, '波斯情仇：伊朗为啥混得这么惨？', 'https://www.youtube.com/watch?v=Zw5m1CNKSgA&t=1622s', '這段影片探討了伊朗為何處於困境的原因。', 1, '2025-06-23 15:11:46', '2025-06-23 15:11:46', '2025-06-18 06:20:51', 744937, 13136, 2723, 'https://i.ytimg.com/vi/Zw5m1CNKSgA/default.jpg', '二爷故事', '2243', NULL),
(122, '【新車介紹】Škoda Superb｜超越質感，平實售價【7Car小七車觀點】', 'https://www.youtube.com/watch?v=iUnQ7v2M9GA', '', 0, NULL, '2025-06-23 15:18:50', '2025-06-23 11:30:36', 1031, 24, 7, 'https://i.ytimg.com/vi/iUnQ7v2M9GA/default.jpg', '7Car小七車觀點', '299', NULL),
(125, '完美表現拿下總冠軍！正處在巔峰期的Shai Gilgeous-Alexander是否真有機會開創王朝？個人進攻完全無解、單場12次助攻激活全隊，雷霆：隊史第一人｜澤北SG', 'https://www.youtube.com/watch?v=wzBg1T61awY', '', 1, '2025-06-23 22:44:43', '2025-06-23 22:44:15', '2025-06-23 19:42:01', 18326, 634, 282, 'https://i.ytimg.com/vi/wzBg1T61awY/default.jpg', '澤北SG', '644', NULL),
(128, '開車去參加盱眙萬人龍蝦宴，阿偉還被邀請上電視了，出息了 #盱眙龍蝦節 #萬人龍蝦宴 #人間煙火 #自駕遊旅行vlog', 'https://www.youtube.com/watch?v=mrxEw6qxqEs', '', 1, '2025-06-24 17:56:40', '2025-06-24 17:42:15', '2025-06-24 16:14:19', 303, 37, 1, 'https://i.ytimg.com/vi/mrxEw6qxqEs/default.jpg', '阿偉燕子旅行記', '489', NULL),
(132, '康是美2025必買好物！超級好用，這7款你一定要知道？！【77老大】', 'https://www.youtube.com/watch?v=LtqyqlmgUTc', '', 1, '2025-06-24 21:42:19', '2025-06-24 21:40:15', '2025-06-24 21:14:43', 1278, 72, 6, 'https://i.ytimg.com/vi/LtqyqlmgUTc/default.jpg', '77老大', '671', NULL),
(133, '花費720k變美，我像個貴婦，他像個肉餅？', 'https://www.youtube.com/watch?v=SQKrLbA2Ets', '', 1, '2025-06-24 21:42:04', '2025-06-24 21:40:18', '2025-06-24 20:01:01', 2845, 85, 11, 'https://i.ytimg.com/vi/SQKrLbA2Ets/default.jpg', 'Dumpling Soda', '1107', NULL),
(135, '最後的奪冠機會！已經迫不及待開始在火箭訓練的Kevin Durant到底有多興奮？完全補強球隊體系、甚至願意降薪續約，主教練：100%支持｜澤北SG', 'https://www.youtube.com/watch?v=m1-BaSVF7Mg', '', 1, '2025-06-24 21:41:46', '2025-06-24 21:40:20', '2025-06-24 19:13:30', 15363, 415, 41, 'https://i.ytimg.com/vi/m1-BaSVF7Mg/default.jpg', '澤北SG', '628', NULL),
(139, 'iOS26 Beta 2 新功能&最大變化｜Q&A｜大耳朵TV', 'https://www.youtube.com/watch?v=y_mld_V0Imo', '', 0, NULL, '2025-06-24 21:40:24', '2025-06-24 19:53:49', 5963, 245, 56, 'https://i.ytimg.com/vi/y_mld_V0Imo/default.jpg', '大耳朵TV', '568', NULL),
(140, '【新車介紹】Range Rover Evoque P250 Dynamic SE 都會魅影版｜車系精簡，英式黑化【7Car小七車觀點】', 'https://www.youtube.com/watch?v=w5hD5h9_3rk', '', 0, NULL, '2025-06-24 21:40:25', '2025-06-24 18:01:26', 710, 29, 2, 'https://i.ytimg.com/vi/w5hD5h9_3rk/default.jpg', '7Car小七車觀點', '457', NULL),
(142, '50碗一起做，大集上的餛飩生產線！大爺喝酒解乏，一天一斤！#food #小吃 #美食 #delicious #路边摊', 'https://www.youtube.com/watch?v=TTm8CZ97npk', '', 0, NULL, '2025-06-26 15:00:53', '2025-06-24 18:00:47', 1721, 13, 1, 'https://i.ytimg.com/vi/TTm8CZ97npk/default.jpg', '西瓜吹雪', '651', NULL),
(145, '中國泡水車泛濫，商販低收高賣坑慘韭菜，政府只管拿稅，監管缺失市場一片混亂，洩洪只為促進消費', 'https://www.youtube.com/watch?v=LQE1moBfysM', '', 0, NULL, '2025-06-26 15:00:55', '2025-06-25 16:52:29', 2114, 41, 3, 'https://i.ytimg.com/vi/LQE1moBfysM/default.jpg', '今日華夏', '482', NULL),
(146, '貴州突發驚人山洪，高速大橋崩然垮塌，卡車司機懸於半空，豆腐渣工慘劇重演，汽車排隊衝進車庫', 'https://www.youtube.com/watch?v=b7TAuLtNI-A', '', 0, NULL, '2025-06-26 15:00:55', '2025-06-24 16:33:43', 14158, 173, 39, 'https://i.ytimg.com/vi/b7TAuLtNI-A/default.jpg', '今日華夏', '487', NULL),
(148, '【突發】日債暴跌危機，即將引爆美債炸彈！全球債務危機前夕，你手上的錢又何去何從？做好最壞打算！', 'https://www.youtube.com/watch?v=zXfZEb1W5bM', '', 0, NULL, '2025-06-26 15:00:59', '2025-06-25 20:01:51', 10177, 124, 17, 'https://i.ytimg.com/vi/zXfZEb1W5bM/default.jpg', '996創業家造雨俠', '861', NULL),
(155, '貧窮比末日更可怕｜隨意畫', 'https://www.youtube.com/watch?v=tGVVARgrrl0', '', 0, NULL, '2025-06-27 08:09:57', '2025-06-26 16:00:58', 18431, 299, 41, 'https://i.ytimg.com/vi/tGVVARgrrl0/default.jpg', '隨意畫', '561', NULL),
(157, '天降異象！貴州排洪滿目瘡痍，民眾財產集體摧毀，車輛橫七豎八一輛壓著一輛，整個縣城商家夜返貧，回天無力', 'https://www.youtube.com/watch?v=BZbr7G_1QQs', '', 0, NULL, '2025-06-27 08:09:59', '2025-06-26 16:48:12', 7180, 130, 50, 'https://i.ytimg.com/vi/BZbr7G_1QQs/default.jpg', '今日華夏', '611', NULL),
(159, '本屆新秀最大黑馬！意外第16順位就被選中的楊瀚森是否真的被高估了？身體天賦並非頂級、卻用試訓征服所有球隊，拓荒者球迷：無法理解｜澤北SG', 'https://www.youtube.com/watch?v=mHvhHmMz7G8', '', 0, NULL, '2025-06-27 08:10:07', '2025-06-26 19:19:14', 105533, 1328, 386, 'https://i.ytimg.com/vi/mHvhHmMz7G8/default.jpg', '澤北SG', '607', NULL),
(160, '半導體大地震？關稅鐵拳讓市值70%陪葬？台股投資人3招逃生！｜ft. 群益投顧總經理范振鴻｜特別企劃 #專家觀點', 'https://www.youtube.com/watch?v=XumMYGCBsoQ', '', 0, NULL, '2025-06-27 08:10:09', '2025-06-26 18:00:06', 2185, 36, 0, 'https://i.ytimg.com/vi/XumMYGCBsoQ/default.jpg', '【CMoney理財寶】官方頻道', '505', NULL),
(162, '【無聊詹免費直播教學第88集】從零開始學技術分析，讓你第一次看線圖就上手💪', 'https://www.youtube.com/watch?v=3ZpFDRGoDFI', '', 0, NULL, '2025-06-27 08:10:09', '2025-06-26 09:26:09', 2371, 45, 0, 'https://i.ytimg.com/vi/3ZpFDRGoDFI/default.jpg', '【CMoney理財寶】官方頻道', '4511', NULL),
(163, '《亮亮艦隊》免費直播｜從資金分布、價格錯位，善用市場「不合理」的地方', 'https://www.youtube.com/watch?v=DANxn3UkIfk', '', 0, NULL, '2025-06-27 08:10:09', '2025-06-26 07:21:17', 567, 6, 0, 'https://i.ytimg.com/vi/DANxn3UkIfk/default.jpg', '【CMoney理財寶】官方頻道', '3553', NULL),
(165, '【ETF】0050週KD飆高還在抱？市值型ETF也要停利！這些投資誤區你中幾個？｜超馬芭樂、葉芷娟｜ETF錢滾錢', 'https://www.youtube.com/watch?v=ndRRlxsj5yg', '', 0, NULL, '2025-06-27 08:10:10', '2025-06-25 18:00:06', 34156, 500, 41, 'https://i.ytimg.com/vi/ndRRlxsj5yg/default.jpg', '【CMoney理財寶】官方頻道', '1841', NULL),
(166, '等等，這個猥瑣的傢伙是......我穿越了嗎！？', 'https://www.youtube.com/watch?v=tVnIFtdkSn4', '', 0, NULL, '2025-06-27 08:10:11', '2025-06-25 19:15:07', 27883, 777, 90, 'https://i.ytimg.com/vi/tVnIFtdkSn4/default.jpg', '老孫聊遊戲', '323', NULL),
(167, '【買前必看】真實使用2周後，總結出7大必買優點 & 一定要瞭解的缺點! | 戴森吸塵器 / Pencilvac / 開箱 / 評測 | 大耳朵TV', 'https://www.youtube.com/watch?v=N2z_AaC6i9U', '', 0, NULL, '2025-06-27 08:10:12', '2025-06-26 20:25:52', 17380, 456, 59, 'https://i.ytimg.com/vi/N2z_AaC6i9U/default.jpg', '大耳朵TV', '747', NULL),
(168, '【特別企劃】AMG 一同共襄盛舉｜七哥搶先看，賽車迷期待已久的電影【7Car小七車觀點】', 'https://www.youtube.com/watch?v=m-y69vfaF-8', '', 0, NULL, '2025-06-27 08:10:12', '2025-06-26 18:30:35', 811, 27, 1, 'https://i.ytimg.com/vi/m-y69vfaF-8/default.jpg', '7Car小七車觀點', '310', NULL),
(169, '【特別企劃】VOLVO EX30 ULTRA｜北歐精品時尚小車！【7Car小七車觀點】', 'https://www.youtube.com/watch?v=pR_ZMacYSow', '', 0, NULL, '2025-06-27 08:10:12', '2025-06-25 18:01:30', 17419, 392, 7, 'https://i.ytimg.com/vi/pR_ZMacYSow/default.jpg', '7Car小七車觀點', '1644', NULL),
(170, '全是套路？机油到底要多久才换- CD的硬核科普', 'https://www.youtube.com/watch?v=DevmOap0pOM&t=305s', '這段影片探討了機油換油的頻率，解釋了換油的原則和時機。', 1, '2025-06-27 15:15:00', '2025-06-27 15:15:00', '2025-06-21 02:09:34', 100305, 1843, 347, 'https://i.ytimg.com/vi/DevmOap0pOM/default.jpg', 'CD', '1134', NULL),
(171, '特朗普打算怎么干美联储? | 一口气了解美联储和白宫的世纪对抗', 'https://www.youtube.com/watch?v=OFmvWLBwDys&t=10s', '這段影片將探討特朗普計劃如何影響美聯儲，並介紹白宮和美聯儲之間的對抗。', 1, '2025-06-27 15:15:06', '2025-06-27 15:15:06', '2025-06-17 21:24:05', 879151, 17791, 713, 'https://i.ytimg.com/vi/OFmvWLBwDys/default.jpg', '小Lin说', '1529', NULL),
(172, '他是杜蕾斯（Durex）技術的奠基者，還是有超能力的靈媒，通靈者？為何他會被官方從歷史中抹去？一個波蘭年輕人的突發奇想，一段杜蕾斯發展歷史中不為人知的故事。', 'https://www.youtube.com/watch?v=kTfLhk0KVJc&t=20s', '這段影片探討了杜蕾斯技術的奠基者，以及他被官方抹去歷史的原因，同時揭示了杜蕾斯發展歷史中不為人知的故事。', 1, '2025-06-27 15:15:12', '2025-06-27 15:15:12', '2024-10-09 19:46:12', 10347, 192, 5, 'https://i.ytimg.com/vi/kTfLhk0KVJc/default.jpg', '胖貓堂主', '848', NULL),
(173, 'Irving大幅降薪！3年1.19億續約獨行俠！只為爭冠！CP3加盟輔佐Flagg？退役前的最後一搏！三狀元聯手沖冠，獨行俠新賽季將統治比賽？深度分析獨行俠休賽期交易。', 'https://www.youtube.com/watch?v=GZ09xVp-8sY', '', 0, NULL, '2025-06-27 15:20:44', '2025-06-25 18:30:05', 43304, 255, 51, 'https://i.ytimg.com/vi/GZ09xVp-8sY/default.jpg', 'Lue有引力', '688', NULL),
(174, 'Amatriciana - Chef Legend in Rome shares Pasta Recipe', 'https://www.youtube.com/watch?v=X6CI5oHdAtE', '', 0, NULL, '2025-06-27 15:20:45', '2025-06-26 13:21:39', 36666, 1214, 70, 'https://i.ytimg.com/vi/X6CI5oHdAtE/default.jpg', 'Aden Films', '1103', NULL),
(175, '無聊詹免費直播第88集精華1：12分鐘快速了解技術分析入門課 一 系統觀念', 'https://www.youtube.com/watch?v=IDt-h9sZCqs', '', 0, NULL, '2025-06-27 15:20:53', '2025-06-26 17:15:02', 247, 2, 0, 'https://i.ytimg.com/vi/IDt-h9sZCqs/default.jpg', '【CMoney理財寶】官方頻道', '666', NULL),
(176, '【阿雪來了】雪寶成長選股 APP｜動能機器人｜阿雪の寶｜業績高成長｜目標價點陣圖', 'https://www.youtube.com/watch?v=imjcYKniG1E', '', 0, NULL, '2025-06-27 15:20:53', '2025-06-25 23:19:26', 5405, 87, 0, 'https://i.ytimg.com/vi/imjcYKniG1E/default.jpg', '【CMoney理財寶】官方頻道', '4297', NULL),
(177, '18年廚齡大廚，馬路邊做一道韭菜炒雞蛋，直接油炸雞蛋，專業#food #探店 #小吃 #西瓜吹雪 #delicious', 'https://www.youtube.com/watch?v=IHJeqgSpfrU', '', 0, NULL, '2025-06-28 06:45:47', '2025-06-27 18:00:34', 1249, 12, 1, 'https://i.ytimg.com/vi/IHJeqgSpfrU/default.jpg', '西瓜吹雪', '640', NULL),
(178, '神的武器無法復制！MJ的後仰跳投有多無解？靜態天賦和Jordan一樣的SGA，拿再多的冠軍也無法超越？完全0空間照樣出手，投籃效率竟碾壓巔峰O\'Neal！深度分析MJ後仰跳投的細節', 'https://www.youtube.com/watch?v=aNIg8XeO9Tw', '', 0, NULL, '2025-06-28 06:45:48', '2025-06-27 18:00:02', 5508, 51, 20, 'https://i.ytimg.com/vi/aNIg8XeO9Tw/default.jpg', 'Lue有引力', '641', NULL),
(179, '量产5nm芯片，为何如此重要？中国5nm工艺深度解读', 'https://www.youtube.com/watch?v=5I3BovCUNNo', '', 0, NULL, '2025-06-28 06:45:49', '2025-06-27 21:21:18', 15328, 1062, 148, 'https://i.ytimg.com/vi/5I3BovCUNNo/default.jpg', '白呀白Talk', '637', NULL),
(180, '貴州遭歷史最大洪災，大量車販趁機發國難財，斷壁殘垣遍地現場一片狼藉，牆內新聞都不敢播', 'https://www.youtube.com/watch?v=2W0gDRNUUBg', '', 0, NULL, '2025-06-28 06:45:50', '2025-06-27 18:43:09', 1541, 38, 18, 'https://i.ytimg.com/vi/2W0gDRNUUBg/default.jpg', '今日華夏', '486', NULL),
(181, '日本啤酒百年大战到底谁赢了？麒麟 朝日 札幌 三得利 日本啤酒战国记', 'https://www.youtube.com/watch?v=8wmC5dqfugQ', '', 1, '2025-06-28 06:46:46', '2025-06-28 06:45:51', '2025-06-27 20:03:28', 6529, 164, 13, 'https://i.ytimg.com/vi/8wmC5dqfugQ/default.jpg', '胖貓堂主', '1280', NULL),
(182, '【突發】2025 香港樓市 即將暴跌？香港 樓價 跌不下來的6大内幕真相！還能 買樓 嗎？ 房地產 房產 香港樓 香港樓盤 香港經濟 樓市泡沫 移民潮 香港人 習近平 李嘉誠 习近平 李家超 經濟崩潰', 'https://www.youtube.com/watch?v=b6HayASBW2E', '', 0, NULL, '2025-06-28 06:45:53', '2025-06-27 20:01:19', 4177, 84, 20, 'https://i.ytimg.com/vi/b6HayASBW2E/default.jpg', '996創業家造雨俠', '2067', NULL),
(183, '为什么日本公寓都设计的如此奇幻？百平米这价格你受的了么！', 'https://www.youtube.com/watch?v=2aLqbWqnLgc', '', 0, NULL, '2025-06-28 06:45:56', '2025-06-27 19:00:04', 11691, 472, 37, 'https://i.ytimg.com/vi/2aLqbWqnLgc/default.jpg', '11区小豪的故事', '431', NULL),
(184, '選秀評價達到A級！全力為Luka Doncic尋找內線搭檔的湖人是否得到了意外之喜？身體天賦超誇張、有望直接成為球隊主力，勇士：同樣滿意｜澤北SG', 'https://www.youtube.com/watch?v=AexLiATowys', '', 0, NULL, '2025-06-28 06:45:57', '2025-06-27 19:13:40', 58647, 772, 45, 'https://i.ytimg.com/vi/AexLiATowys/default.jpg', '澤北SG', '778', NULL),
(185, '看不懂的怪船頭！四條拖輪護航進港，為什麼拖輪力氣會這麼大？', 'https://www.youtube.com/watch?v=qDGYSW3neps', '', 0, NULL, '2025-06-28 06:45:58', '2025-06-27 19:01:39', 25236, 1038, 88, 'https://i.ytimg.com/vi/qDGYSW3neps/default.jpg', '李船長筆記', '492', NULL),
(186, '無聊詹免費直播第88集精華2：11分鐘快速了解技術分析入門課 一 趨勢均線', 'https://www.youtube.com/watch?v=HulycGEWbYQ', '', 0, NULL, '2025-06-28 06:45:59', '2025-06-28 00:00:28', 221, 12, 0, 'https://i.ytimg.com/vi/HulycGEWbYQ/default.jpg', '【CMoney理財寶】官方頻道', '635', NULL),
(187, '【精華】高股息ETF選哪檔？00919不香了？剩0056可以買？｜超馬芭樂、葉芷娟｜ETF錢滾錢', 'https://www.youtube.com/watch?v=6p-tw9xcP4k', '', 0, NULL, '2025-06-28 06:46:00', '2025-06-27 19:00:19', 6156, 71, 11, 'https://i.ytimg.com/vi/6p-tw9xcP4k/default.jpg', '【CMoney理財寶】官方頻道', '578', NULL),
(188, '“大人，時代變了”！童年火爆的激龜快打，小兵實現逆襲！', 'https://www.youtube.com/watch?v=XRVYN4Nf1so', '', 0, NULL, '2025-06-28 06:46:01', '2025-06-27 19:24:28', 25059, 831, 28, 'https://i.ytimg.com/vi/XRVYN4Nf1so/default.jpg', '老孫聊遊戲', '909', NULL),
(189, '【新車介紹】Kia Sportage Adventure Edition｜帶著質感去露營【7Car小七車觀點】', 'https://www.youtube.com/watch?v=uyt45HLTVLo', '', 0, NULL, '2025-06-28 06:46:02', '2025-06-27 18:01:40', 4166, 67, 12, 'https://i.ytimg.com/vi/uyt45HLTVLo/default.jpg', '7Car小七車觀點', '331', NULL),
(190, '十年內超越中國？伊朗應該成爲下一個經濟奇跡 | 1K圖解', 'https://www.youtube.com/watch?v=b4lWetPPWCU', '', 0, NULL, '2025-06-28 06:46:03', '2025-06-27 20:20:05', 3579, 182, 58, 'https://i.ytimg.com/vi/b4lWetPPWCU/default.jpg', '1K圖解', '980', NULL),
(191, '三巨頭解散危機？2人拒絕湖人續約！Reaves拒絕湖人4年8920萬續約！DFS試水自由市場！補強中鋒遭全聯盟針對！湖人徹底崩盤？', 'https://www.youtube.com/watch?v=SomO_JRBPyY', '', 1, '2025-06-29 20:09:27', '2025-06-29 09:36:39', '2025-06-28 18:00:42', 8054, 59, 38, 'https://i.ytimg.com/vi/SomO_JRBPyY/default.jpg', 'Lue有引力', '621', NULL),
(192, 'MJ被諷不會三分？一怒直接破三分紀錄！三分球真的是Michael Jordan的唯一弱點嗎？惹怒MJ後果多嚴重！面對打法超像自己的對手，卻用最不擅長的方式完勝！不要質疑神，越重要的比賽，MJ三分球越準', 'https://www.youtube.com/watch?v=pkDY3FVBJxM', '', 0, NULL, '2025-06-29 09:36:39', '2025-06-28 12:00:57', 2041, 17, 1, 'https://i.ytimg.com/vi/pkDY3FVBJxM/default.jpg', 'Lue有引力', '4272', NULL),
(193, '貴州再遭洩洪襲擊，全民棄家大逃亡，現場一片混亂，車輛排長龍整個城區只出不進', 'https://www.youtube.com/watch?v=sRQnfQXmSgQ', '', 0, NULL, '2025-06-29 09:36:40', '2025-06-28 16:45:28', 12070, 161, 43, 'https://i.ytimg.com/vi/sRQnfQXmSgQ/default.jpg', '今日華夏', '488', NULL),
(194, '移民8年，是时候和加拿大说再见了！', 'https://www.youtube.com/watch?v=BC7e-S_-dfo', '', 1, '2025-06-29 17:46:11', '2025-06-29 09:36:44', '2025-06-28 09:00:22', 92291, 1777, 1021, 'https://i.ytimg.com/vi/BC7e-S_-dfo/default.jpg', '56BelowTV 零下56', '2701', NULL),
(195, '【精華】下半年台股財富密碼！有機會再創新高嗎？關稅戰、以伊戰爭、Fed降息動向全解析!｜股市錢滾錢', 'https://www.youtube.com/watch?v=Km_KLrANs90', '', 0, NULL, '2025-06-29 09:36:48', '2025-06-28 10:00:13', 995, 22, 1, 'https://i.ytimg.com/vi/Km_KLrANs90/default.jpg', '【CMoney理財寶】官方頻道', '297', NULL),
(196, '我来到日本大阪 他们都不说英语  保姆级攻略 维森来了', 'https://www.youtube.com/watch?v=Y3UnbZz_BEA', '', 1, '2025-06-29 09:37:13', '2025-06-29 09:36:49', '2025-06-28 18:00:51', 12089, 335, 89, 'https://i.ytimg.com/vi/Y3UnbZz_BEA/default.jpg', '维森来了Wilson', '663', NULL),
(197, '當初為了Wemban，馬刺付出了多大代價？深度解析文班亞馬攻防兩端技術特點以及優劣勢，為何他將是統治NBA未來十年的男人！', 'https://www.youtube.com/watch?v=nBifdSk5mDU', '', 0, NULL, '2025-06-29 17:45:36', '2025-06-29 12:01:02', 1747, 16, 2, 'https://i.ytimg.com/vi/nBifdSk5mDU/default.jpg', 'Lue有引力', '3271', NULL),
(198, '超10萬人連夜撤離！半個中國陷入全力抗洪，水位已經直逼四樓，救援隊來了都要跑路，小微再喊話拉動貴州經濟', 'https://www.youtube.com/watch?v=YOZQHoo5y24', '', 0, NULL, '2025-06-29 17:45:37', '2025-06-29 11:52:16', 2453, 64, 32, 'https://i.ytimg.com/vi/YOZQHoo5y24/default.jpg', '今日華夏', '493', NULL),
(199, '【精華】下半年台股3產業逆勢爆發！台積電2奈米受惠股；重電不受關稅影響；鎖定亞馬遜、Meta概念股｜范振鴻、葉芷娟｜股市錢滾錢', 'https://www.youtube.com/watch?v=CiaSb3PtGF8', '', 0, NULL, '2025-06-29 17:45:45', '2025-06-29 10:00:11', 416, 7, 0, 'https://i.ytimg.com/vi/CiaSb3PtGF8/default.jpg', '【CMoney理財寶】官方頻道', '193', NULL),
(200, '他可能是整個NBA，最不想和Curry當隊友的球員。Curry沒做到的事情，他卻做到了！從曾經無人問津，如今大器晚成！Curry：一直在被作比較，但我們是最好的兄弟。', 'https://www.youtube.com/watch?v=ZMe1UvCvq2I', '', 0, NULL, '2025-06-29 19:58:51', '2025-06-29 18:00:51', 798, 20, 1, 'https://i.ytimg.com/vi/ZMe1UvCvq2I/default.jpg', 'Lue有引力', '656', NULL),
(201, 'Michelin Star Pasta in Paris - Gourmet Food in France', 'https://www.youtube.com/watch?v=eDaywHADY8A', '', 0, NULL, '2025-06-29 19:58:52', '2025-06-29 18:25:13', 694, 69, 3, 'https://i.ytimg.com/vi/eDaywHADY8A/default.jpg', 'Aden Films', '1751', NULL),
(202, '正式宣布開始重建！連續失去冠軍隊友的Jayson Tatum是否還有爭冠希望？一筆交易節省4000萬奢侈稅、新援竟也擁有超強得分能力，綠軍：繼續補強｜澤北SG', 'https://www.youtube.com/watch?v=IDtyMYM1U7g', '', 0, NULL, '2025-06-29 19:58:58', '2025-06-29 19:00:26', 7237, 176, 17, 'https://i.ytimg.com/vi/IDtyMYM1U7g/default.jpg', '澤北SG', '639', NULL),
(203, '男主剛登場就死了！這遊戲的劇情有多離譜？', 'https://www.youtube.com/watch?v=4c_S2czTt9A', '', 0, NULL, '2025-06-29 19:59:01', '2025-06-29 19:15:00', 3759, 203, 21, 'https://i.ytimg.com/vi/4c_S2czTt9A/default.jpg', '老孫聊遊戲', '538', NULL),
(204, 'Google I/O 2025：搜索帝国的自我革命与AI翻身仗【线下探会】', 'https://www.youtube.com/watch?v=-85ZV63y9PI&t=179s', '這段影片內容主要是關於2025年Google I/O大會，探討了Google在人工智慧領域的自我革命和AI的發展。', 1, '2025-06-29 20:00:57', '2025-06-29 20:00:57', '2025-05-26 10:21:03', 159149, 2971, 215, 'https://i.ytimg.com/vi/-85ZV63y9PI/default.jpg', '硅谷101', '1305', NULL),
(205, '全球面積第12大的國家，為何沙烏地82%的人都居住在這兩塊區域？#好奇羅盤 #地理趣聞 #地理', 'https://www.youtube.com/watch?v=vOv7ayViPEk', '這段影片探討了全球面積第12大的國家，沙烏地阿拉伯，為何有82%的人口都居住在兩個特定區域的原因。透過好奇羅盤和地理趣聞的方式，解釋了這個現象背後的原因。', 1, '2025-06-29 20:01:09', '2025-06-29 20:01:09', '2025-01-09 16:06:52', 278472, 1823, 108, 'https://i.ytimg.com/vi/vOv7ayViPEk/default.jpg', '好奇羅盤', '603', NULL),
(206, '83歲蔡瀾，9條人間清醒的臨終遺言，解決了人生中80%的内耗和焦慮！人生的意義只有一點！', 'https://www.youtube.com/watch?v=5V_t2c-6qSY', '', 0, NULL, '2025-06-29 20:06:31', '2025-06-29 20:00:17', 90, 4, 1, 'https://i.ytimg.com/vi/5V_t2c-6qSY/default.jpg', '996創業家造雨俠', '1686', NULL),
(207, '8-bit computer update', 'https://www.youtube.com/watch?v=HyznrdDSSGM', '這段影片是關於8位元電腦的最新更新。', 1, '2025-06-29 20:22:18', '2025-06-29 20:22:18', '2016-03-10 03:25:27', 2524448, 59685, 1207, 'https://i.ytimg.com/vi/HyznrdDSSGM/default.jpg', 'Ben Eater', '413', NULL),
(208, 'A simple BIOS for my breadboard computer', 'https://www.youtube.com/watch?v=0q6Ujn_zNH8', '這段影片介紹如何為自己的麵包板電腦建立一個簡單的BIOS。', 1, '2025-06-29 20:22:33', '2025-06-29 20:22:33', '2024-01-07 23:01:26', 395114, 18475, 475, 'https://i.ytimg.com/vi/0q6Ujn_zNH8/default.jpg', 'Ben Eater', '1313', NULL),
(209, 'The Homemade GameBoy! (Arduboy)', 'https://www.youtube.com/watch?v=yVzIvbx_vC0', '這段影片介紹如何製作一台自製的遊戲機，名為Arduboy，外觀類似GameBoy。', 1, '2025-06-29 20:35:29', '2025-06-29 20:35:29', '2018-08-05 16:25:21', 71641, 1570, 61, 'https://i.ytimg.com/vi/yVzIvbx_vC0/default.jpg', 'The Retro Future', '425', NULL),
(210, 'Inside the cleanroom - How computer chips get made!', 'https://www.youtube.com/watch?v=aBDJQ9NYTEU&t=1s', '這段影片將介紹在無塵室內製造電腦晶片的過程。', 1, '2025-06-29 22:46:29', '2025-06-29 22:46:29', '2023-12-20 21:42:53', 17921, 691, 75, 'https://i.ytimg.com/vi/aBDJQ9NYTEU/default.jpg', 'Zero To ASIC Course', '1030', NULL),
(211, '放大20万倍，这是3纳米芯片最深层的秘密【玄戒O1解剖报告】', 'https://www.youtube.com/watch?v=Q9oJnTmpMg8', '這段影片將放大20萬倍觀察3納米芯片的最深層秘密。', 1, '2025-06-30 14:09:23', '2025-06-30 14:09:23', '2025-06-18 21:13:29', 103832, 3303, 451, 'https://i.ytimg.com/vi/Q9oJnTmpMg8/default.jpg', '谈三圈', '1177', NULL);

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `channels`
--
ALTER TABLE `channels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_channels_category` (`category_id`);

--
-- 資料表索引 `channel_categories`
--
ALTER TABLE `channel_categories`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `search_history`
--
ALTER TABLE `search_history`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `youtube_url` (`youtube_url`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `channels`
--
ALTER TABLE `channels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `channel_categories`
--
ALTER TABLE `channel_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `search_history`
--
ALTER TABLE `search_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=212;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `channels`
--
ALTER TABLE `channels`
  ADD CONSTRAINT `fk_channels_category` FOREIGN KEY (`category_id`) REFERENCES `channel_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
