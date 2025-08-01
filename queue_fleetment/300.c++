using namespace std;

int main() {
    int year;
    cout << "Enter a year (AD): ";
    cin >> year;

    // ตรวจสอบเงื่อนไขปีอธิกสุรทิน
    if ((year % 4 == 0 && year % 100 != 0) || (year % 400 == 0)) {
        cout << "Leap Year" << endl;
    } else {
        cout << "Not a Leap Year" << endl;
    }

    return 0;
}
