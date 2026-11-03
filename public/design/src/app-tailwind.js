/* =========================================================================
   Tâm Minh - vanilla JS for the Tailwind build.
   Renders all data-driven lists + wires interactions + scroll-reveal.
   ========================================================================= */
(function () {
  'use strict';
  /* ---------- Icons: Lucide (ISC) - lucide.dev ---------- */
  var PATHS = {
    sparkle:'<path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/>',
    face:'<path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><path d="M9 9h.01"/><path d="M15 9h.01"/>',
    shield:'<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
    bolt:'<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/>',
    yinyang:'<path d="m16.24 7.76-1.804 5.411a2 2 0 0 1-1.265 1.265L7.76 16.24l1.804-5.411a2 2 0 0 1 1.265-1.265z"/><circle cx="12" cy="12" r="10"/>',
    book:'<path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>',
    upload:'<path d="M12 13v8"/><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="m8 17 4-4 4 4"/>',
    arrow:'<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
    message:'<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
    user:'<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    users:'<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    headset:'<path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/>',
    mountain:'<path d="m8 3 4 8 5-5 5 15H2L8 3z"/>',
    star:'<path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/>',
    check:'<path d="M20 6 9 17l-5-5"/>',
    send:'<path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/>',
    plus:'<path d="M5 12h14"/><path d="M12 5v14"/>',
    messenger:'<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M8 10h.01"/><path d="M12 10h.01"/><path d="M16 10h.01"/>',
    phone:'<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
    mail:'<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
    pin:'<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
    download:'<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
    share:'<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.59 13.51 6.83 3.98"/><path d="m15.41 6.51-6.82 3.98"/>',
    printer:'<path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect x="6" y="14" width="12" height="8" rx="1"/>',
    clock:'<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
    calendar:'<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>',
    badge:'<path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/>',
    chevron:'<path d="m9 18 6-6-6-6"/>',
    arrowleft:'<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
    moon:'<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/><path d="M20 3v4"/><path d="M22 5h-4"/>',
    trend:'<path d="M16 7h6v6"/><path d="m22 7-8.5 8.5-5-5L2 17"/>',
    file:'<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/>',
    x:'<path d="M18 6 6 18"/><path d="m6 6 12 12"/>'
  };
  function icon(name, size, stroke) {
    size = size || 24; stroke = stroke || 1.75;
    return '<svg width="' + size + '" height="' + size + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' + stroke + '" stroke-linecap="round" stroke-linejoin="round">' + (PATHS[name] || '') + '</svg>';
  }
  // Hydrate every data-ic placeholder
  function hydrateIcons(root) {
    (root || document).querySelectorAll('[data-ic]').forEach(function (el) {
      if (el.dataset.icDone) return;
      el.innerHTML = icon(el.dataset.ic, +el.dataset.sz || 24, +el.dataset.stroke || 1.75);
      el.dataset.icDone = '1';
    });
  }

  window.TM = { icons: hydrateIcons };
  /* ---------- Data ---------- */
  var NAV = [
    { id:'home', label:'Trang chủ' }, { id:'face', label:'Xem tướng số' }, { id:'palm', label:'Xem chỉ tay' },
    { id:'fengshui', label:'Kiến thức phong thủy' }, { id:'experts', label:'Chuyên gia' }, { id:'contact', label:'Liên hệ' }
  ];
  var NGU_HANH = [
    { glyph:'金', name:'Kim', desc:'Mặt vuông, da sáng - chính trực, quyết đoán, giỏi làm chủ.' },
    { glyph:'木', name:'Mộc', desc:'Mặt dài thanh tú - nhân hậu, sáng tạo, luôn vươn lên.' },
    { glyph:'水', name:'Thủy', desc:'Mặt tròn đầy đặn - linh hoạt, thông tuệ, ứng biến khéo.' },
    { glyph:'火', name:'Hỏa', desc:'Mặt nhọn, nét sắc sảo - nhiệt huyết, có tài dẫn dắt.' },
    { glyph:'土', name:'Thổ', desc:'Mặt dày, thần thái vững chãi - bao dung, điềm đạm, đáng tin.' }
  ];
  var THAN_KHI_SAC = [
    { letter:'神', name:'Thần', desc:'Ánh mắt và tinh thần - phản chiếu nội lực, ý chí và chiều sâu tâm hồn. Mắt có thần là người minh mẫn, bền chí, dễ thành đạt.' },
    { letter:'氣', name:'Khí', desc:'Hơi thở và nội khí - biểu hiện sức khỏe, sinh lực và vận trình thịnh suy hiện tại. Khí đầy, điềm đạm là dấu hiệu vận đang lên.' },
    { letter:'色', name:'Sắc', desc:'Nước da và sắc diện - hé lộ cát hung, tài lộc và biến động sắp tới. Sắc hồng nhuận báo tin vui, sắc xỉn tối cần giữ gìn.' }
  ];
  var TAM_DINH = [
    { glyph:'上', name:'Thượng đình', range:'Trán → chân mày', meaning:'Tổ tiên, trí tuệ, vận đầu đời (15–30)' },
    { glyph:'中', name:'Trung đình', range:'Chân mày → đầu mũi', meaning:'Bản thân, sự nghiệp, vận trung niên (31–50)' },
    { glyph:'下', name:'Hạ đình', range:'Đầu mũi → cằm', meaning:'Hậu vận, con cháu, phúc lộc cuối đời (51+)' }
  ];
  var NGU_QUAN = [
    { glyph:'目', name:'Mắt', subtitle:'Giám sát quan', desc:'Tâm tính, trí tuệ, vận 35–40' },
    { glyph:'眉', name:'Lông mày', subtitle:'Bảo thọ quan', desc:'Tình cảm, anh em, vận 31–34' },
    { glyph:'耳', name:'Tai', subtitle:'Thái thính quan', desc:'Phúc lộc, thọ mệnh, vận 1–14' },
    { glyph:'鼻', name:'Mũi', subtitle:'Thẩm biện quan', desc:'Tài lộc, vợ chồng, vận 41–50' },
    { glyph:'口', name:'Miệng', subtitle:'Xuất nạp quan', desc:'Phúc khẩu, con cái, vận 51+' }
  ];
  var THAP_NHI_CUNG = [
    { g:'命', n:'Mệnh cung', d:'Bản mệnh, tổng quan cuộc đời' }, { g:'官', n:'Quan lộc cung', d:'Sự nghiệp, chức vụ, công danh' },
    { g:'財', n:'Tài bạch cung', d:'Tài lộc, tiền bạc, của cải' }, { g:'遷', n:'Thiên di cung', d:'Di chuyển, xuất ngoại, đổi vận' },
    { g:'福', n:'Phúc đức cung', d:'Phúc khí, hưởng thụ, đức độ' }, { g:'妻', n:'Phu thê cung', d:'Hôn nhân, vợ chồng, duyên phận' },
    { g:'子', n:'Tử tức cung', d:'Con cái, hậu duệ, sinh nở' }, { g:'兄', n:'Huynh đệ cung', d:'Anh chị em, bạn bè thân cận' },
    { g:'僕', n:'Nô bộc cung', d:'Cấp dưới, người làm, đối tác' }, { g:'父', n:'Phụ mẫu cung', d:'Cha mẹ, tổ tiên, gia đạo' },
    { g:'田', n:'Điền trạch cung', d:'Nhà cửa, đất đai, bất động sản' }, { g:'疾', n:'Tật ách cung', d:'Sức khỏe, bệnh tật, tai ương' }
  ];
  var PHONG_THUY = [
    { ic:'sparkle', t:'Màu sắc phù hợp', d:'Màu tương sinh, tương hợp với bản mệnh để hút tài lộc, may mắn' },
    { ic:'mountain', t:'Hướng phong thủy', d:'Hướng nhà, hướng bàn làm việc, hướng ngủ tốt cho từng năm' },
    { ic:'star', t:'Đồ vật phong thủy', d:'Vật phẩm cát tường, đá quý, linh vật hộ mệnh nên đeo bên người' },
    { ic:'users', t:'Tuổi hợp làm ăn', d:'Tuổi tương sinh để hợp tác, kết giao, tránh tuổi xung khắc' },
    { ic:'message', t:'Số & ngày tốt', d:'Con số may mắn, ngày giờ hoàng đạo để khởi sự, ký kết, xuất hành' },
    { ic:'shield', t:'Hóa giải sát khí', d:'Cách bố trí, trấn yểm hóa giải hung tinh và điểm xấu trong nhà' }
  ];
  var ADVICE = [
    { k:'Tâm pháp', v:'Tu tâm dưỡng tính, thiền định, hành thiện - gốc rễ của mọi cải vận.' },
    { k:'Hành động', v:'Việc nên làm, việc nên tránh trong từng đại vận và lưu niên.' },
    { k:'Thời điểm', v:'Năm tốt, tháng tốt, ngày giờ hoàng đạo cho các quyết định lớn.' },
    { k:'Vật phẩm', v:'Linh vật, vật phẩm phong thủy, hướng nhà - hỗ trợ thực tế.' }
  ];
  var EXPERTS_1 = [
    { glyph:'明', name:'Thầy Minh Quang', role:'Chuyên gia tướng số', years:30, sessions:1287, photo:'assets/thay-tuong-so.png' },
    { glyph:'太', name:'Thầy Tấn Phát', role:'Chuyên gia tướng số', years:25, sessions:956, photo:'assets/thay-tuong-so.png' },
    { glyph:'禄', name:'Thầy Thiên Lộc', role:'Chuyên gia tướng số', years:20, sessions:743, photo:'assets/thay-tuong-so.png' }
  ];
  var EXPERTS_2 = [
    { glyph:'玄', name:'Thầy Huyền Lâm', role:'Chuyên gia tử vi', years:30, sessions:1542 },
    { glyph:'寶', name:'Thầy Bửu Tâm', role:'Chuyên gia tử vi', years:25, sessions:1123 },
    { glyph:'覺', name:'Thầy Giác An', role:'Chuyên gia tử vi', years:20, sessions:876 },
    { glyph:'德', name:'Thầy Minh Đức', role:'Chuyên gia tử vi', years:18, sessions:689 }
  ];
  var LUAN_GIAI = [
    { title:'Luận giải tình duyên', eyebrow:'Vận trình tình cảm', items:['Luận giải bộ sao Đào Hoa - Hồng Loan.','Dự báo thời điểm vượng duyên, gặp gỡ.','Diễn biến tâm lý và chuyện lứa đôi.','Đánh giá mức độ hợp/khắc của đối phương.'] },
    { title:'Luận giải sự nghiệp & tài lộc', eyebrow:'Công danh - Tiền tài', items:['Phân tích chuyên sâu cung Quan & Tài.','Cơ hội thăng chức, chuyển việc, đổi nghề.','Nguồn thu chính và các dòng tiền phụ.','Thời điểm vàng để đầu tư, khởi sự.'] },
    { title:'Luận giải vận hạn', eyebrow:'Cát hung & Biến động', items:['Sao chiếu mệnh & Hạn niên','Phân tích hạn Tam Tai, Thái Tuế, Kim Lâu','Tiểu hạn các tháng','Phương pháp hóa giải, xu cát tị hung.'] },
    { title:'Luận giải hôn nhân & gia đạo', eyebrow:'Hạnh phúc gia đình', items:['Biến động cung Phu Thê','Mối quan hệ với cha mẹ, anh em','Tin vui về con cái (Cung Tử Tức)','Lời khuyên giữ lửa hôn nhân'] },
    { title:'Luận giải sức khoẻ', eyebrow:'Thể chất & Tinh thần', items:['Dự báo cung Tật Ách','Hạn về xe cộ, sông nước hoặc dao kéo','Sức khỏe tinh thần và áp lực trong năm','Chế độ dưỡng sinh phù hợp với mệnh lý'] }
  ];
  var KB = [
    { aspect:'Độ chính xác', neg:{ t:'Sai lệch thông tin', d:'Hệ thống tự động dễ mắc lỗi múi giờ, lịch âm, dẫn đến an sao sai hoàn toàn.' }, pos:{ t:'An sao chính xác 100%', d:'Kiểm tra kỹ lưỡng độ số, múi giờ theo hệ thống Tử Vi truyền thống chuẩn mực.' } },
    { aspect:'Nội dung', neg:{ t:'Rập khuôn', d:'Cắt ghép "văn mẫu" có sẵn - một kết quả áp dụng cho hàng ngàn người.' }, pos:{ t:'Cá nhân hóa tuyệt đối', d:'Không dùng văn mẫu. Mỗi bài luận được viết mới dựa trên hoàn cảnh riêng của bạn.' } },
    { aspect:'Cách tiếp cận', neg:{ t:'Khô khan, khó hiểu', d:'Lạm dụng thuật ngữ cổ, văn phong xa cách, thiếu tính ứng dụng thực tế.' }, pos:{ t:'Kết hợp Tâm lý học', d:'Giải mã căn nguyên nội tâm - giúp bạn hiểu "tại sao", không chỉ là "số mệnh thế nào".' } },
    { aspect:'Văn phong', neg:{ t:'Rời rạc, lan man', d:'Luận giải thiếu trọng tâm, khiến bạn khó nắm bắt cốt lõi vấn đề.' }, pos:{ t:'Thấu cảm, gần gũi', d:'Như một người bạn tri kỷ đang đối thoại - không phán xét, không giáo điều.' } }
  ];
  var STATS = [
    { num:'8.500+', label:'Khuôn mặt đã luận giải' }, { num:'4.9/5', label:'Điểm đánh giá trung bình' },
    { num:'97%', label:'Khách hàng giới thiệu bạn bè' }, { num:'28', label:'Thầy tướng số & chuyên gia chỉ tay' }
  ];
  var REVIEWS = [
    { rating:5, quote:'Báo cáo của Tâm Minh chi tiết đến từng cung mệnh, đọc xong mình thấy hiểu chính mình hơn - kiểu tâm lý học chứ không phải bói toán mê tín. Câu chuyện rất riêng tư, không có cảm giác "văn mẫu".', name:'Nguyễn Thanh Linh', role:'Nhà thiết kế · Hà Nội', service:'Luận giải tử vi trọn đời', avatar:'L', color:'copper' },
    { rating:5, quote:'Công nghệ phân tích khuôn mặt nhận diện tướng mạo khá chính xác, nhưng điều mình ấn tượng là phần Thầy luận giải bằng văn phong gần gũi. Mình hỏi gì cũng được trả lời cặn kẽ, không phải kiểu chốt sale dịch vụ.', name:'Trần Minh Tuấn', role:'Giám đốc kinh doanh · TP.HCM', service:'Xem tướng số cùng Thầy Minh Quang', avatar:'T', color:'navy' },
    { rating:5, quote:'Cô từng đi xem ở nhiều nơi nhưng nội dung na ná nhau. Tâm Minh hỏi rất kỹ về hoàn cảnh, bài luận viết riêng cho mình. Phần phong thủy nhà ở thực sự hữu ích, đã áp dụng và thấy gia đạo êm ấm hơn.', name:'Lê Thị Hương', role:'Chủ doanh nghiệp · Đà Nẵng', service:'Phong thủy nhà ở', avatar:'H', color:'copper' },
    { rating:5, quote:'Mình hoài nghi mấy kiểu xem tướng online, nhưng Tâm Minh khác - họ giải thích "tại sao" chứ không phán xét. Phần lời khuyên cải vận rất thực tế: nên làm gì, tránh gì, không có vẽ vời.', name:'Phạm Đức Anh', role:'Lập trình viên · Hà Nội', service:'Luận giải nhân tướng', avatar:'Đ', color:'navy' },
    { rating:5, quote:'Thầy Huyền Lâm tư vấn cho mình về sự nghiệp và hôn nhân rất tâm huyết. Mỗi lần nhắn tin Thầy đều phản hồi nhanh, lời khuyên đúng trọng tâm. Đáng tiền và đáng trân quý.', name:'Vũ Thu Hoa', role:'Giáo viên · Cần Thơ', service:'Tư vấn 1-1 với Thầy Huyền Lâm', avatar:'H', color:'copper' },
    { rating:5, quote:'Thầy xem chỉ tay cho mình rất kỹ - đường sinh đạo, trí đạo, tâm đạo đều giải thích cặn kẽ, nhiều điều nói trúng những chuyện mình đã trải qua. Mức độ chuyên môn ở đây thực sự nghiêm túc.', name:'Hoàng Văn Khải', role:'Doanh nhân · TP.HCM', service:'Xem chỉ tay với chuyên gia', avatar:'K', color:'navy' }
  ];
  var POSTS = [
    { cat:'Tướng pháp', glyph:'相', bg:'copper', title:'Đọc khuôn mặt qua thần - khí - sắc: ba tầng năng lượng người xưa tin cậy', excerpt:'Trước cả khi đếm ngũ quan, các Thầy tướng số cổ truyền đã dạy phải đọc "thần" trước tiên. Bài viết giải mã ba lớp năng lượng trên khuôn mặt mỗi người.', author:'Thầy Minh Quang', date:'12 thg 5, 2026', minutes:8 },
    { cat:'Tử vi', glyph:'紫', bg:'navy', title:'Năm Bính Ngọ 2026: vận khí 12 con giáp và những điều cần lưu ý', excerpt:'Lưu niên 2026 đem theo sự dịch chuyển của Thái Tuế, Tam Tai và Kim Lâu. Phân tích chi tiết vận khí từng tuổi cùng phương pháp hóa giải.', author:'Thầy Huyền Lâm', date:'08 thg 5, 2026', minutes:12 },
    { cat:'Phong thủy', glyph:'風', bg:'cream', title:'Bài trí phòng làm việc theo bản mệnh - cách hút tài lộc không cần linh vật', excerpt:'Ngũ hành bản mệnh quyết định hướng bàn, màu sơn, ánh sáng và cây xanh. Hướng dẫn tự kiểm tra phòng làm việc trong 10 phút.', author:'Thầy An Khang', date:'02 thg 5, 2026', minutes:6 }
  ];
  var TRUST = [
    { ic:'shield', t:'Bảo mật thông tin', s:'Cam kết bảo mật tuyệt đối thông tin cá nhân của bạn' },
    { ic:'yinyang', t:'Luận giải chính xác', s:'Công nghệ phân tích khuôn mặt cùng chuyên gia hàng đầu' },
    { ic:'users', t:'Chuyên gia uy tín', s:'Đội ngũ chuyên gia nhiều năm kinh nghiệm, tận tâm' },
    { ic:'headset', t:'Hỗ trợ tận tâm', s:'Hỗ trợ nhanh chóng 24/7 mọi lúc mọi nơi' }
  ];
  var FAQS = [
    { q:'Tâm Minh là dịch vụ như thế nào?', a:'Tâm Minh là nền tảng xem tướng số và chỉ tay, kết hợp công nghệ phân tích khuôn mặt với đội ngũ chuyên gia nhân tướng hàng đầu Việt Nam. Bạn có thể nhận báo cáo luận giải chi tiết hoặc đặt tư vấn 1-1 với các Thầy có 18-30 năm kinh nghiệm.' },
    { q:'Công nghệ phân tích khuôn mặt chính xác đến đâu?', a:'Hệ thống đo đạc và tái tạo chính xác hàng trăm điểm mốc trên khuôn mặt - tỷ lệ tam đình, hình dáng ngũ quan, đường nét và khí sắc - rồi đối chiếu với tướng pháp truyền thống. Phần luận giải sâu về vận hạn, tài lộc, tình duyên do Thầy trực tiếp phụ trách.' },
    { q:'Cần chuẩn bị ảnh như thế nào?', a:'Chụp chính diện, đủ sáng, không đeo kính, không dùng filter làm mịn da. Ảnh rõ nét giúp công nghệ tái tạo khuôn mặt chính xác hơn, từ đó kết quả luận giải sát với tướng mạo thật của bạn.' },
    { q:'Thông tin cá nhân và ảnh của tôi được bảo mật ra sao?', a:'Ảnh chân dung được mã hóa và chỉ dùng cho việc phân tích duy nhất của bạn - không lưu trữ lâu dài, không chia sẻ cho bên thứ ba. Bạn có thể yêu cầu xóa toàn bộ dữ liệu bất cứ lúc nào.' },
    { q:'Báo cáo được gửi trong bao lâu?', a:'Báo cáo phân tích khuôn mặt: vài phút. Báo cáo có Thầy luận giải: 24-48 giờ. Tư vấn 1-1 trực tiếp: đặt lịch trong vòng 24 giờ. Mức độ chuyên sâu càng cao thì thời gian luận giải càng kỹ.' },
    { q:'Chi phí mỗi loại dịch vụ là bao nhiêu?', a:'Xem tướng cơ bản: miễn phí. Luận giải nhân tướng chuyên sâu: 199.000đ. Luận giải có Thầy phản biện: từ 599.000đ. Xem chỉ tay / tư vấn 1-1 với Thầy: từ 1.200.000đ/giờ tùy chuyên gia. Tất cả minh bạch trước khi thanh toán.' },
    { q:'Tôi không hài lòng với kết quả thì sao?', a:'Tâm Minh cam kết hoàn 100% phí dịch vụ trong vòng 7 ngày nếu bạn không hài lòng với báo cáo, không cần lý do. Riêng các buổi tư vấn 1-1 có thể được xếp lại với Thầy khác trong cùng cấp độ.' },
    { q:'Xem tướng có phải mê tín không?', a:'Tâm Minh không xem tướng số như "định mệnh không thể đổi". Chúng tôi coi đó là công cụ tự nhận thức - giúp bạn hiểu xu hướng tính cách, điểm mạnh, điểm yếu - để chủ động cải vận, chứ không phán xét.' }
  ];
  var FOOTER_COLS = [
    { title:'Dịch vụ', links:['Xem tướng số','Xem chỉ tay','Phong thủy nhà ở','Tư vấn 1-1 với Thầy','Xem ngày tốt','Đặt tên con'] },
    { title:'Khám phá', links:['Trang chủ','Đội ngũ chuyên gia','Kiến thức tướng số','Câu chuyện khách hàng','Cộng tác viên','Chương trình affiliate'] },
    { title:'Hỗ trợ', links:['Câu hỏi thường gặp','Chính sách bảo mật','Điều khoản dịch vụ','Chính sách hoàn tiền','Hướng dẫn sử dụng','Liên hệ'] }
  ];

  var $ = function (s, r) { return (r || document).querySelector(s) || document.createElement('div'); };
  var nf = function (n) { return n.toLocaleString('de-DE'); };

  /* ---------- Render ---------- */
  function render() {
    // Nav (desktop + drawer)
    $('#topnav').innerHTML = NAV.map(function (l, i) {
      return '<a href="#" class="whitespace-nowrap border-b-2 py-1.5 text-sm transition-colors ' + (i === 0 ? 'border-copper-400 font-semibold text-copper-600' : 'border-transparent text-ink-500 hover:text-copper-600') + '">' + l.label + '</a>';
    }).join('');
    $('#drawernav').innerHTML = NAV.map(function (l, i) {
      return '<a href="#" data-drawer-link class="block border-l-[3px] py-3.5 px-6 font-sans text-base transition-colors ' + (i === 0 ? 'border-copper-400 bg-copper-50 font-semibold text-copper-700' : 'border-transparent text-ink-600 hover:bg-cream-50 hover:text-copper-700') + '">' + l.label + '</a>';
    }).join('');

    // Ngũ hành
    $('#list-nguhanh').innerHTML = NGU_HANH.map(function (e) {
      return '<li class="flex items-start gap-3.5"><span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-copper-200 bg-copper-50 font-serif text-xl font-semibold text-copper-700">' + e.glyph + '</span><div><div class="mb-0.5 text-base font-semibold text-ink-600">Hành ' + e.name + '</div><div class="text-sm leading-snug text-ink-300">' + e.desc + '</div></div></li>';
    }).join('');

    // Thần khí sắc
    $('#list-thankhi').innerHTML = THAN_KHI_SAC.map(function (t, i) {
      return '<div class="flex items-start gap-4 pt-3 pb-4 ' + (i ? 'border-t border-cream-300' : '!pt-1') + '"><div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-navy-500 font-serif text-2xl font-semibold text-copper-400">' + t.letter + '</div><div><div class="mb-1 font-serif text-lg font-semibold text-ink-600">' + t.name + '</div><div class="text-sm leading-snug text-ink-500">' + t.desc + '</div></div></div>';
    }).join('');

    // Tam đình
    $('#list-tamdinh').innerHTML = TAM_DINH.map(function (t) {
      return '<div class="flex items-center gap-5"><span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-copper-200 bg-copper-50 font-serif text-xl font-semibold text-copper-700">' + t.glyph + '</span><div><div class="mb-0.5 text-base font-semibold text-ink-600">' + t.name + '</div><div class="mb-1 text-sm tracking-wide text-copper-600">' + t.range + '</div><div class="text-sm leading-snug text-ink-300">' + t.meaning + '</div></div></div>';
    }).join('');

    // Ngũ quan
    $('#list-nguquan').innerHTML = NGU_QUAN.map(function (q, i) {
      var border = i < NGU_QUAN.length - 1 ? ' lg:border-r lg:border-cream-300' : '';
      return '<div class="flex flex-col items-center gap-2 px-2 text-center lg:px-5' + border + '"><div class="mb-1.5 flex h-[60px] w-[60px] items-center justify-center rounded-full border-[1.5px] border-copper-400 bg-cream-100 font-serif text-3xl font-medium text-copper-700 lg:h-[72px] lg:w-[72px] lg:text-4xl">' + q.glyph + '</div><div class="font-serif text-lg font-semibold leading-tight text-ink-600 lg:text-xl">' + q.name + '</div><div class="text-sm font-semibold uppercase tracking-[0.12em] text-copper-600">' + q.subtitle + '</div><div class="max-w-[160px] text-sm leading-snug text-ink-300">' + q.desc + '</div></div>';
    }).join('');

    // 12 cung
    $('#list-cung').innerHTML = THAP_NHI_CUNG.map(function (c, i) {
      return '<div class="group relative flex flex-col gap-1 rounded-[10px] border border-cream-300 bg-cream-50 p-4 transition-all duration-200 hover:-translate-y-px hover:border-copper-300 hover:bg-white hover:shadow-soft-sm"><div class="absolute right-3.5 top-3 text-sm font-semibold tracking-wide text-ink-200">' + String(i + 1).padStart(2, '0') + '</div><div class="mb-1.5 font-serif text-3xl font-semibold leading-none text-copper-700">' + c.g + '</div><div class="text-base font-semibold text-ink-600">' + c.n + '</div><div class="text-sm leading-snug text-ink-300">' + c.d + '</div></div>';
    }).join('');

    // Phong thủy
    $('#list-phongthuy').innerHTML = PHONG_THUY.map(function (p) {
      return '<div class="flex items-start gap-3"><div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border-[1.5px] border-copper-400 text-copper-400">' + icon(p.ic, 20) + '</div><div><div class="mb-1 text-base font-semibold text-ink-600">' + p.t + '</div><div class="text-sm leading-snug text-ink-300">' + p.d + '</div></div></div>';
    }).join('');

    // Advice
    $('#list-advice').innerHTML = ADVICE.map(function (a, i) {
      return '<div class="grid grid-cols-[80px_1fr] items-baseline gap-3 py-3 sm:grid-cols-[90px_1fr] sm:gap-4 ' + (i ? 'border-t border-cream-300' : 'pt-1') + '"><div class="text-sm font-bold uppercase tracking-[0.12em] text-copper-600">' + a.k + '</div><div class="text-base leading-relaxed text-ink-500">' + a.v + '</div></div>';
    }).join('');

    // Experts
    function expertCard(e) {
      var av = e.photo ? '<img src="' + e.photo + '" alt="' + e.name + '" class="h-36 w-36 flex-shrink-0 rounded-lg bg-cream-200 object-cover object-top sm:h-40 sm:w-40"/>' : '<div class="flex h-36 w-36 flex-shrink-0 items-center justify-center rounded-lg bg-navy-500 font-serif text-5xl font-semibold text-copper-400 sm:h-40 sm:w-40">' + e.glyph + '</div>';
      return '<article class="card flex items-stretch gap-5 rounded-[10px] border border-cream-300 bg-base-100 p-5 shadow-soft-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-copper-200 hover:shadow-soft-md">' + av +
        '<div class="flex min-w-0 flex-1 flex-col"><h4 class="m-0 mb-1 font-sans text-lg font-semibold leading-tight text-ink-600 sm:text-xl">' + e.name + '</h4><div class="text-sm text-ink-300">' + e.role + '</div><div class="mt-2.5 flex flex-col gap-1 text-sm text-ink-500"><span>Kinh nghiệm: <b class="font-medium">' + e.years + ' năm</b></span><span>Lượt tư vấn: <b class="font-medium">' + nf(e.sessions) + '</b></span></div>' +
        '<button data-expert=\'' + JSON.stringify({ glyph:e.glyph, name:e.name, role:e.role }).replace(/'/g, '&#39;') + '\' class="mt-auto flex w-full items-center justify-center gap-2 rounded-md bg-copper-400 px-4 py-2.5 text-sm font-semibold uppercase tracking-wide text-white shadow-soft-sm transition-all duration-200 hover:bg-copper-500 hover:shadow-soft-md active:scale-[0.98]">' + icon('messenger', 16, 1.8) + ' Kết nối</button></div></article>';
    }
    $('#grid-experts-1').innerHTML = EXPERTS_1.map(function (e) { return '<div class="reveal">' + expertCard(e) + '</div>'; }).join('');
    $('#grid-experts-2').innerHTML = EXPERTS_2.map(function (e) { return '<div class="reveal">' + expertCard(e) + '</div>'; }).join('');

    // Luận giải
    $('#grid-luangiai').innerHTML = LUAN_GIAI.map(function (c) {
      var items = c.items.map(function (it, k) {
        var last = k === c.items.length - 1;
        return '<li class="text-sm leading-snug ' + (last ? 'italic text-ink-300' : 'text-ink-500') + '">' + it + '</li>';
      }).join('');
      return '<div class="reveal card tm-card group border-copper-200"><div class="font-serif text-xl font-medium leading-tight tracking-[-0.005em] text-ink-600 sm:text-2xl">' + c.title + '</div><div class="-mt-1 font-serif text-sm italic text-copper-600">' + c.eyebrow + '</div><ul class="mt-1 flex list-none flex-col gap-2.5 p-0">' + items + '</ul></div>';
    }).join('') +
    '<div class="reveal card flex flex-col items-center justify-center gap-4 rounded-xl border border-copper-200 bg-cream-300 p-8 text-center shadow-soft-sm"><div class="font-serif text-2xl font-medium text-copper-700">+ Khám phá</div><div class="font-serif text-2xl font-medium italic leading-tight text-ink-600 lg:text-3xl">Tổng quan vận mệnh<br/>năm 2026</div><button data-toast="Đang phân tích - kết quả sẽ hiển thị trong giây lát…" class="mt-1.5 inline-flex items-center gap-2 rounded-full bg-copper-400 px-8 py-3 text-base font-medium text-white shadow-soft-sm transition-colors duration-200 hover:bg-copper-500">Luận giải lá số</button></div>';

    // Khác biệt rows
    var xSvg = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 flex-shrink-0"><circle cx="12" cy="12" r="10"/><path d="M8.5 8.5l7 7M15.5 8.5l-7 7"/></svg>';
    var cSvg = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 flex-shrink-0"><circle cx="12" cy="12" r="10"/><path d="M8 12.5l3 3 5-6"/></svg>';
    $('#kb-rows').innerHTML = KB.map(function (row, i) {
      var num = String(i + 1).padStart(2, '0');
      return '<div class="grid border-t border-cream-300 first:border-t-0 lg:grid-cols-[180px_1fr_1fr]">' +
        '<div class="flex items-baseline gap-3 border-b border-cream-300 bg-cream-50 px-5 py-3.5 lg:flex-col lg:justify-center lg:gap-1.5 lg:border-b-0 lg:border-r lg:px-6 lg:py-6"><span class="font-serif text-sm tracking-[0.16em] text-copper-500">' + num + '</span><span class="font-serif text-base font-semibold text-ink-600 lg:text-lg">' + row.aspect + '</span></div>' +
        '<div class="relative flex items-start gap-3.5 border-b border-dashed border-cream-300 px-5 py-4 lg:border-b-0 lg:border-r-[1.5px] lg:border-solid lg:border-copper-300 lg:px-7 lg:py-[22px]"><span class="text-ink-200">' + xSvg + '</span><div class="pr-16 lg:pr-0"><div class="mb-1.5 font-serif text-base font-semibold leading-tight text-ink-300 line-through decoration-copper-200 lg:text-lg">' + row.neg.t + '</div><div class="text-sm leading-relaxed text-ink-300 lg:text-sm">' + row.neg.d + '</div></div><span class="absolute right-4 top-4 font-sans text-xs font-bold uppercase tracking-[0.14em] text-ink-200 lg:hidden">Khác</span></div>' +
        '<div class="relative flex items-start gap-3.5 bg-copper-50 px-5 py-4 lg:px-7 lg:py-[22px]"><span class="text-copper-600">' + cSvg + '</span><div class="pr-20 lg:pr-0"><div class="mb-1.5 font-serif text-base font-semibold leading-tight text-copper-700 lg:text-lg">' + row.pos.t + '</div><div class="text-sm leading-relaxed text-ink-500 lg:text-sm">' + row.pos.d + '</div></div><span class="absolute right-4 top-4 font-sans text-xs font-bold uppercase tracking-[0.14em] text-copper-700 lg:hidden">Tâm Minh</span></div>' +
        '</div>';
    }).join('');

    // Stats
    $('#grid-stats').innerHTML = STATS.map(function (s, i) {
      var border = i < STATS.length - 1 ? ' lg:border-r lg:border-cream-300' : '';
      var topBorder = i >= 2 ? ' border-t border-cream-300 lg:border-t-0' : '';
      var rightBorder = i % 2 === 0 ? ' border-r border-cream-300 lg:border-r' : '';
      return '<div class="flex flex-col items-center gap-1.5 px-4 py-3.5 text-center lg:py-0 lg:px-7' + rightBorder + topBorder + (i === 1 ? ' lg:border-r' : '') + '"><div class="font-serif text-3xl font-semibold leading-none tracking-[-0.02em] text-copper-700 lg:text-4xl">' + s.num + '</div><div class="max-w-[180px] text-xs leading-snug text-ink-300 lg:text-xs">' + s.label + '</div></div>';
    }).join('');

    // Reviews
    function stars(n) {
      var out = '';
      for (var i = 0; i < 5; i++) out += '<svg width="16" height="16" viewBox="0 0 24 24" fill="' + (i < n ? 'currentColor' : 'none') + '" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.8 6.2 20.9l1.1-6.5L2.6 9.8l6.5-.9L12 3z"/></svg>';
      return '<div class="inline-flex gap-1 text-copper-500">' + out + '</div>';
    }
    $('#grid-reviews').innerHTML = REVIEWS.map(function (r) {
      var av = r.color === 'navy' ? 'bg-navy-500 text-copper-400' : 'bg-copper-50 text-copper-700 border border-copper-200';
      return '<figure class="reveal relative m-0 flex flex-col gap-[18px] rounded-2xl border border-cream-300 bg-white p-7 transition-all duration-300 hover:-translate-y-0.5 hover:border-copper-200 hover:shadow-soft-md">' + stars(r.rating) +
        '<blockquote class="m-0 flex-1 text-sm leading-relaxed text-ink-600"><span class="mr-0.5 align-[-10px] font-serif text-4xl leading-[0] text-copper-300">“</span>' + r.quote + '</blockquote>' +
        '<figcaption class="flex items-start gap-3.5 border-t border-cream-300 pt-[18px]"><div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full font-serif text-lg font-semibold ' + av + '">' + r.avatar + '</div><div class="min-w-0"><div class="text-sm font-semibold leading-snug text-ink-600">' + r.name + '</div><div class="text-xs leading-snug text-ink-300">' + r.role + '</div><div class="mt-1 text-xs font-medium leading-snug text-copper-600">✦ ' + r.service + '</div></div></figcaption></figure>';
    }).join('');

    // Blog
    var bgMap = {
      copper:'bg-gradient-to-br from-copper-100 to-cream-300 text-copper-700',
      navy:'bg-gradient-to-br from-navy-500 to-[#1f4778] text-copper-400',
      cream:'bg-gradient-to-br from-cream-300 to-copper-50 text-copper-600'
    };
    var catBg = function (bg) { return bg === 'navy' ? 'bg-cream-50/95' : 'bg-white/95'; };
    $('#grid-blog').innerHTML = POSTS.map(function (p) {
      return '<article class="reveal group flex cursor-pointer flex-col overflow-hidden rounded-2xl border border-cream-300 bg-white transition-all duration-300 hover:-translate-y-0.5 hover:border-copper-200 hover:shadow-soft-md">' +
        '<div class="relative flex h-[180px] items-center justify-center overflow-hidden lg:h-[200px] ' + bgMap[p.bg] + '"><span class="relative z-10 font-serif text-8xl font-medium leading-none tracking-[-0.04em] lg:text-8xl" style="text-shadow:0 2px 16px rgba(0,0,0,.06)">' + p.glyph + '</span>' +
        '<svg class="pointer-events-none absolute -right-14 -top-14 h-60 w-60" viewBox="0 0 80 80" fill="none"><circle cx="40" cy="40" r="36" stroke="currentColor" stroke-opacity=".25"/><circle cx="40" cy="40" r="28" stroke="currentColor" stroke-opacity=".18"/><circle cx="40" cy="40" r="20" stroke="currentColor" stroke-opacity=".12"/></svg>' +
        '<span class="absolute bottom-3.5 left-4 rounded ' + catBg(p.bg) + ' px-3 py-1.5 font-sans text-xs font-bold uppercase tracking-[0.14em] text-copper-700 backdrop-blur">' + p.cat + '</span></div>' +
        '<div class="flex flex-1 flex-col gap-3 p-6"><h3 class="m-0 font-serif text-lg font-semibold leading-snug tracking-[-0.005em] text-ink-600 lg:text-xl">' + p.title + '</h3><p class="m-0 line-clamp-3 flex-1 text-sm leading-relaxed text-ink-300">' + p.excerpt + '</p><div class="flex flex-wrap items-center gap-1.5 border-t border-cream-300 pt-3.5 text-xs text-ink-300"><span class="font-semibold text-copper-700">' + p.author + '</span><span class="text-ink-200">•</span><span>' + p.date + '</span><span class="text-ink-200">•</span><span>' + p.minutes + ' phút đọc</span></div></div></article>';
    }).join('');

    // Trust
    $('#grid-trust').innerHTML = TRUST.map(function (t) {
      return '<div class="reveal flex items-center gap-3.5"><div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full border-[1.5px] border-copper-400 text-copper-400">' + icon(t.ic, 24) + '</div><div><div class="mb-0.5 text-sm font-semibold text-ink-600">' + t.t + '</div><div class="text-xs leading-snug text-ink-300">' + t.s + '</div></div></div>';
    }).join('');

    // FAQ accordion (custom CSS: .faq-item / .faq-ans)
    $('#faq-list').innerHTML = FAQS.map(function (f, i) {
      return '<div class="faq-item border-t border-cream-300 first:border-t-0' + (i === 0 ? ' open' : '') + '">' +
        '<button class="faq-btn flex w-full items-start justify-between gap-4 px-6 py-5 text-left">' +
          '<span class="font-serif text-base font-medium leading-snug text-ink-600 lg:text-lg">' + f.q + '</span>' +
          '<span class="faq-chevron mt-1 flex-shrink-0 text-copper-400 transition-transform duration-300">' +
            '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>' +
          '</span>' +
        '</button>' +
        '<div class="faq-ans"><div><div class="px-6 pb-5 text-sm leading-relaxed text-ink-500"><p class="m-0 pr-6">' + f.a + '</p></div></div></div>' +
      '</div>';
    }).join('');

    // Footer columns
    $('#footer-cols').innerHTML = FOOTER_COLS.map(function (col) {
      var links = col.links.map(function (l) { return '<li><a href="#" class="text-sm leading-snug text-white/70 transition-colors hover:text-copper-400">' + l + '</a></li>'; }).join('');
      return '<div><div class="mb-[18px] font-sans text-xs font-bold uppercase tracking-[0.14em] text-copper-400">' + col.title + '</div><ul class="m-0 flex list-none flex-col gap-2.5 p-0">' + links + '</ul></div>';
    }).join('');

    // Date selects
    function opts(label, arr) { return '<option>' + label + '</option>' + arr.map(function (v) { return '<option>' + v + '</option>'; }).join(''); }
    var days = []; for (var d = 1; d <= 31; d++) days.push(d);
    var months = []; for (var m = 1; m <= 12; m++) months.push(m);
    var years = []; for (var y = 0; y < 80; y++) years.push(2010 - y);
    var hours = []; for (var h = 0; h < 24; h++) hours.push(h);
    var mins = []; for (var mi = 0; mi < 60; mi += 5) mins.push(mi);
    document.querySelectorAll('[data-fill]').forEach(function (sel) {
      var f = sel.dataset.fill;
      if (f === 'day') sel.innerHTML = opts('Ngày', days);
      else if (f === 'month') sel.innerHTML = opts('Tháng', months);
      else if (f === 'year') sel.innerHTML = opts('Năm', years);
      else if (f === 'hour') sel.innerHTML = opts('Giờ', hours);
      else if (f === 'min') sel.innerHTML = opts('Phút', mins);
    });

    hydrateIcons();
  }

  /* ---------- Interactions ---------- */
  var toastTimer;
  function showToast(msg) {
    var t = $('#toast');
    $('#toast-msg').textContent = msg;
    t.classList.remove('opacity-0', 'translate-y-3');
    t.classList.add('opacity-100', 'translate-y-0');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () {
      t.classList.add('opacity-0', 'translate-y-3');
      t.classList.remove('opacity-100', 'translate-y-0');
    }, 2600);
  }

  function openDrawer(open) {
    var b = $('#burger'), sp = b.children;
    if (open) {
      document.body.classList.add('drawer-open');
      b.setAttribute('aria-expanded', 'true');
      sp[0].style.transform = 'translateY(6.5px) rotate(45deg)';
      sp[1].style.opacity = '0';
      sp[2].style.transform = 'translateY(-6.5px) rotate(-45deg)';
    } else {
      document.body.classList.remove('drawer-open');
      b.setAttribute('aria-expanded', 'false');
      sp[0].style.transform = ''; sp[1].style.opacity = ''; sp[2].style.transform = '';
    }
  }

  function wire() {
    // Toast triggers (delegated)
    document.addEventListener('click', function (e) {
      var t = e.target.closest('[data-toast]');
      if (t) { e.preventDefault(); showToast(t.dataset.toast); }
    });

    // Drawer
    $('#burger').addEventListener('click', function () { openDrawer(!document.body.classList.contains('drawer-open')); });
    $('#drawer-close').addEventListener('click', function () { openDrawer(false); });
    $('#scrim').addEventListener('click', function () { openDrawer(false); });
    document.addEventListener('click', function (e) { if (e.target.closest('[data-drawer-link]')) openDrawer(false); });

    // FAQ accordion
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('.faq-btn'); if (!btn) return;
      var item = btn.closest('.faq-item'); if (!item) return;
      var isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item.open').forEach(function (i) { i.classList.remove('open'); });
      if (!isOpen) item.classList.add('open');
    });
    // (placeholder for original comment)

    // Forms
    ['#form-face', '#form-tuvi'].forEach(function (sel) {
      var f = $(sel);
      if (f) f.addEventListener('submit', function (e) { e.preventDefault(); showToast('Đang phân tích - kết quả sẽ hiển thị trong giây lát…'); });
    });
    ['#form-chitay'].forEach(function (sel) {
      var f = $(sel);
      if (f) f.addEventListener('submit', function (e) { e.preventDefault(); showToast('Đã nhận yêu cầu đặt lịch - Tâm Minh sẽ liên hệ xác nhận sớm nhất.'); });
    });
    // File name display
    var ff = $('#face-file');
    if (ff) ff.addEventListener('change', function () { if (ff.files[0]) $('#face-fname').textContent = ff.files[0].name; });

    // Newsletter
    var nForm = $('#news-form');
    if (nForm) nForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var em = $('#news-email');
      if (!em.value.trim()) return;
      $('#news-note').textContent = 'Cảm ơn bạn - chúng tôi sẽ gửi bản tin đầu tiên trong tuần này.';
      em.value = '';
      setTimeout(function () { $('#news-note').textContent = 'Không spam. Hủy đăng ký bất cứ lúc nào.'; }, 3200);
    });

    // Expert modal (delegated)
    document.addEventListener('click', function (e) {
      var b = e.target.closest('[data-expert]'); if (!b) return;
      var ex = JSON.parse(b.dataset.expert.replace(/&#39;/g, "'"));
      $('#modal-glyph').textContent = ex.glyph;
      $('#modal-name').textContent = ex.name;
      $('#modal-role').textContent = ex.role;
      $('#modal-body').textContent = 'Cảm ơn bạn đã quan tâm. ' + ex.name + ' sẽ liên hệ với bạn trong thời gian sớm nhất. Trong khi chờ đợi, bạn có thể để lại câu hỏi.';
      $('#modal-send').dataset.who = ex.name;
      var sc = $('#modal-scrim'); sc.classList.remove('hidden'); sc.classList.add('flex');
    });
    function closeModal() { var sc = $('#modal-scrim'); sc.classList.add('hidden'); sc.classList.remove('flex'); }
    $('#modal-close').addEventListener('click', closeModal);
    $('#modal-scrim').addEventListener('click', function (e) { if (e.target === $('#modal-scrim')) closeModal(); });
    $('#modal-send').addEventListener('click', function () { var who = $('#modal-send').dataset.who; closeModal(); showToast('Đã gửi tin nhắn tới ' + who); });

    // Header shadow on scroll
    var hdr = $('#hdr');
    window.addEventListener('scroll', function () {
      if (window.scrollY > 8) hdr.classList.add('shadow-soft-sm'); else hdr.classList.remove('shadow-soft-sm');
    }, { passive: true });
  }

  /* ---------- Scroll reveal (AOS-driven) ---------- */
  function setupReveal() {
    // Default animation for every reveal element (respect explicit data-aos in markup).
    document.querySelectorAll('.reveal').forEach(function (el) {
      if (!el.hasAttribute('data-aos')) el.setAttribute('data-aos', 'fade-up');
    });

    // Stagger children inside [data-stagger] groups.
    document.querySelectorAll('[data-stagger]').forEach(function (g) {
      var kids = g.querySelectorAll(':scope > .reveal');
      kids.forEach(function (k, i) {
        if (!k.hasAttribute('data-aos-delay')) k.setAttribute('data-aos-delay', String(i * 80));
      });
    });

    if (!window.AOS) {
      // Fallback: just show everything if the library failed to load.
      document.querySelectorAll('.reveal').forEach(function (e) { e.classList.add('aos-init', 'aos-animate'); });
      return;
    }

    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    AOS.init({
      duration: 750,
      easing: 'ease-out-cubic',
      once: true,
      offset: 90,
      anchorPlacement: 'top-bottom',
      disable: reduce
    });
    // Re-measure after fonts/images settle so positions stay accurate.
    window.addEventListener('load', function () { AOS.refresh(); });
  }

  /* ---------- Boot ---------- */
  function init() { render(); wire(); setupReveal(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
