class Vyakti {
  final int id;
  final String prathamNaam;
  final String? madhyaNaam;
  final String kulNaam;
  final String ling;
  final bool jeevit;
  final String? janmTithiGregorian;
  final String? janmTithiVs;
  final String? gotra;
  final String? photoUrl;

  Vyakti({
    required this.id,
    required this.prathamNaam,
    this.madhyaNaam,
    required this.kulNaam,
    required this.ling,
    required this.jeevit,
    this.janmTithiGregorian,
    this.janmTithiVs,
    this.gotra,
    this.photoUrl,
  });

  factory Vyakti.fromJson(Map<String, dynamic> json) {
    return Vyakti(
      id: json['id'],
      prathamNaam: json['pratham_naam'],
      madhyaNaam: json['madhya_naam'],
      kulNaam: json['kul_naam'],
      ling: json['ling'],
      jeevit: json['jeevit'] == 1,
      janmTithiGregorian: json['janm_tithi_gregorian'],
      janmTithiVs: json['janm_tithi_vs'],
      gotra: json['gotra'],
      photoUrl: json['photo_url'],
    );
  }

  String get fullNaam => "$prathamNaam ${madhyaNaam ?? ''} $kulNaam".trim();
}
