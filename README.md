# Claims To OpenEMR


## X12 Specifications

	.		\\--[ Communications Transport Protocol ]---------------/
	ISA		\\--[ Interchange Control Header ]------------------/	|
	GS		\\--[ Functional Group Header ]-----------------/	|	|
	ST		\\--[ Transaction Set Header ]--------------/	|	|	|
				+-----------------------------------+	|	|	|	|
				|	Detail Segments					|	|	|	|	|
				|	for example, Claim Payment		|	|	|	|	|
				+-----------------------------------+	|	|	|	|
				+-----------------------------------+	|	|	|	|
				|	Detail Segments					|	|	|	|	|
				|	for example, Claim Payment		|	|	|	|	|
				+-----------------------------------+	|	|	|	|
	SE		//--[ Transaction Set Trailer ]-------------\	|	|	|
	GE		//--[ Functional Group Trailer ]----------------\	|	|
	GS		\\--[ Functional Group Header ]-----------------/	|	|
	ST		\\--[ Transaction Set Header ]--------------/	|	|	|
				+-----------------------------------+	|	|	|	|
				|	Detail Segments					|	|	|	|	|
				|	for example, Benefit Enrollment	|	|	|	|	|
				+-----------------------------------+	|	|	|	|
	SE		//--[ Transaction Set Trailer ]-------------\	|	|	|
	GE		//--[ Functional Group Trailer ]----------------\	|	|
	IEA		//--[ Interchange Control Trailer ]-----------------\	|
	.		//--[ Communications Transport Trailer ]----------------\


### 837 - Health Care Claim		Functional Group ID: HC

#### Loops

	- 1000
	- 2000
		- 2010
		- 2300
			- 2305
			- 2310
			- 2320
				- 2330
			- 2400
				- 2410
				- 2420
				- 2430
				- 2440


#### Segment / Loop Table

	.			| 1000	| 2000	| 2010	| 2300	| 2305	| 2310	| 2320	| 2330	| 2400	| 2410	| 2420	| 2430	| 2440	|
			----+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+
		+	AMT	|		|		|		|	*	|		|		|	*	|		|	*	|		|		|	*	|		|
		+	CAS	|		|		|		|		|		|		|	*	|		|		|		|		|	*	|		|
		?	CL1	|		|		|		|	*	|		|		|		|		|		|		|		|		|		|
	*	+	CLM	|		|		|		|	*	|		|		|		|		|		|		|		|		|		|
		+	CN1	|		|		|		|	*	|		|		|		|		|	*	|		|		|		|		|
		+	CR1	|		|		|		|	*	|		|		|		|		|	*	|		|		|		|		|
		+	CR2	|		|		|		|	*	|		|		|		|		|	*	|		|		|		|		|
		+	CR3	|		|		|		|	*	|		|		|		|		|	*	|		|		|		|		|
		?	CR4	|		|		|		|	*	|		|		|		|		|	*	|		|		|		|		|
		-	CR5	|		|		|		|	*	|		|		|		|		|	*	|		|		|		|		|
		?	CR6	|		|		|		|	*	|		|		|		|		|		|		|		|		|		|
		-	CR7	|		|		|		|		|	*	|		|		|		|		|		|		|		|		|
		?	CR8	|		|		|		|	*	|		|		|		|		|		|		|		|		|		|
		+	CRC	|		|		|		|	*	|		|		|		|		|	*	|		|		|		|		|
		+	CTP	|		|		|		|		|		|		|		|		|		|	*	|		|		|		|
		+	CUR	|		|	*	|		|		|		|		|		|		|		|		|		|		|		|
	*	+	DMG	|		|		|	*	|		|		|		|	*	|		|		|		|		|		|		|
		?	DN1	|		|		|		|	*	|		|		|		|		|		|		|		|		|		|
		?	DN2	|		|		|		|	*	|		|		|		|		|		|		|		|		|		|
		?	DSB	|		|		|		|	*	|		|		|		|		|		|		|		|		|		|
			----+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+
				| 1000	| 2000	| 2010	| 2300	| 2350	| 2310	| 2320	| 2330	| 2400	| 2410	| 2420	| 2430	| 2440	|
			----+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+
	*	+	DTP	|		|	*	|		|	*	|		|		|		|	*	|	*	|		|		|	*	|		|
		+	FRM	|		|		|		|		|		|		|		|		|		|		|		|		|	*	|
		+	HCP	|		|		|		|	*	|		|		|		|		|	*	|		|		|		|		|
	*	+	HI	|		|		|		|	*	|		|		|		|		|	*	|		|		|		|		|
	*	+	HL	|		|	*	|		|		|		|		|		|		|		|		|		|		|		|
		-	HSD	|		|		|		|		|	*	|		|		|		|	*	|		|		|		|		|
		?	IMM	|		|		|		|		|		|		|		|		|	*	|		|		|		|		|
	*	+	K3	|		|		|		|	*	|		|		|		|		|	*	|		|		|		|		|
		+	LIN	|		|		|		|		|		|		|		|		|		|	*	|		|		|		|
		+	LQ	|		|		|		|		|		|		|		|		|		|		|		|		|	*	|
	*	+	LX	|		|		|		|		|		|		|		|		|	*	|		|		|		|		|
		+	MEA	|		|		|		|		|		|		|		|		|	*	|		|		|		|		|
		?	MIA	|		|		|		|		|		|		|	*	|		|		|		|		|		|		|
		+	MOA	|		|		|		|		|		|		|	*	|		|		|		|		|		|		|
		?	N2	|	*	|		|	*	|		|		|	*	|		|	*	|		|		|	*	|		|		|
	*	+	N3	|	*	|		|	*	|		|		|	*	|		|	*	|		|		|	*	|		|		|
	*	+	N4	|	*	|		|	*	|		|		|	*	|		|	*	|		|		|	*	|		|		|
	*	+	NM1	|	*	|		|	*	|		|		|	*	|		|	*	|		|		|	*	|		|		|
	*	+	NTE	|		|		|		|	*	|		|		|		|		|	*	|		|		|		|		|
		+	OI	|		|		|		|		|		|		|	*	|		|		|		|		|		|		|
			----+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+
				| 1000	| 2000	| 2010	| 2300	| 2350	| 2310	| 2320	| 2330	| 2400	| 2410	| 2420	| 2430	| 2440	|
			----+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+
	*	+	PAT	|		|	*	|		|		|		|		|		|		|		|		|		|		|		|
		+	PER	|	*	|		|	*	|		|		|	*	|		|	*	|		|		|	*	|		|		|
	*	+	PRV	|		|	*	|		|		|		|	*	|		|		|		|		|	*	|		|		|
		+	PS1	|		|		|		|		|		|		|		|		|	*	|		|		|		|		|
	*	+	PWK	|		|		|		|	*	|		|		|		|		|	*	|		|		|		|		|
		+	QTY	|		|		|		|	*	|		|		|		|		|	*	|		|		|		|		|
	*	+	REF	|	*	|		|	*	|	*	|		|	*	|		|	*	|	*	|	*	|	*	|		|		|
	*	+	SBR	|		|	*	|		|		|		|		|	*	|		|		|		|		|		|		|
	*	+	SV1	|		|		|		|		|		|		|		|		|	*	|		|		|		|		|
		?	SV2	|		|		|		|		|		|		|		|		|	*	|		|		|		|		|
		?	SV3	|		|		|		|		|		|		|		|		|	*	|		|		|		|		|
		?	SV4	|		|		|		|		|		|		|		|		|	*	|		|		|		|		|
		+	SV5	|		|		|		|		|		|		|		|		|	*	|		|		|		|		|
		?	SV6	|		|		|		|		|		|		|		|		|	*	|		|		|		|		|
		?	SV7	|		|		|		|		|		|		|		|		|	*	|		|		|		|		|
		+	SVD	|		|		|		|		|		|		|		|		|		|		|		|	*	|		|
		?	TOO	|		|		|		|		|		|		|		|		|	*	|		|		|		|		|
		?	UR	|		|		|		|	*	|		|		|		|		|		|		|		|		|		|
	.		----+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+-------+


#### 837 Standard

##### Table 1 - Header

	.	POS #	SEG ID	NAME												REQ DES		MAX USE		LOOP REPEAT
		0050	ST		Transaction Set Header								M			1
	*	0100	BHT		Beginning of Hierarchical Transaction				M			1
		0150	REF		Reference Information								O			3
					\\--[ LOOP ID - 1000 ]----------------------------------------------------------[ 10 ]--------------\
	*	0200	NM1		Individual or Organizational Name					O			1								|
		0250	N2		Additional Name Information							O			2								|
	*	0300	N3		Party Location										O			2								|
	*	0350	N4		Geographic Location									O			1								|
	*	0400	REF		Reference Information								O			2								|
		0450	PER		Administrative Communications Contact				O			2								|
					//--------------------------------------------------------------------------------------------------/


##### Table 2 - Detail

	.	POS #	SEG ID	NAME												REQ DES		MAX USE		LOOP REPEAT
					\\--[ LOOP ID - 2000 ]----------------------------------------------------------[ >1 ]--------------\
		0010	HL		Hierarchical Level									M			1								|
	*	0030	PRV		Provider Information								O			1								|
	*	0050	SBR		Subscriber Information								O			1								|
	*	0070	PAT		Patient Information									O			1								|
		0090	DTP		Date or Time or Period								O			5								|
		0100	CUR		Currency											O			1								|
					\\--[ LOOP ID - 2010 ]----------------------------------------------------------[ 10 ]----------\	|
	*	0150	NM1		Individual or Organizational Name					O			1							|	|
		0200	N2		Additional Name Information							O			2							|	|
	*	0250	N3		Party Location										O			2							|	|
	*	0300	N4		Geographic Location									O			1							|	|
	*	0320	DMG		Demographic Information								O			1							|	|
	*	0350	REF		Reference Information								O			20							|	|
		0400	PER		Administrative Communications Contact				O			2							|	|
					//----------------------------------------------------------------------------------------------/	|
					\\--[ LOOP ID - 2300 ]----------------------------------------------------------[ 100 ]---------\	|
	*	1300	CLM		Health Claim										O			1							|	|
	*	1350	DTP		Date or Time or Period								O			150							|	|
		1400	CL1		Claim Codes											O			1							|	|
		1450	DN1		Orthodontic Information								O			1							|	|
		1500	DN2		Tooth Summary										O			35							|	|
		1550	PWK		Paperwork											O			10							|	|
		1600	CN1		Contract Information								O			1							|	|
		1650	DSB		Disability Information								O			1							|	|
		1700	UR		Peer Review Organization or Utilization Review		O			1							|	|
		1750	AMT		Monetary Amount Information							O			40							|	|
	*	1800	REF		Reference Information								O			30							|	|
		1850	K3		File Information									O			10							|	|
	*	1900	NTE		Note/Special Instruction							O			20							|	|
		1950	CR1		Ambulance Certification								O			1							|	|
		2000	CR2		Chiropractic Certification							O			1							|	|
		2050	CR3		Durable Medical Equipment Certification				O			1							|	|
		2100	CR4		Enteral or Parenteral Therapy Certification			O			3							|	|
		2150	CR5		Oxygen Therapy Certification						O			1							|	|
		2160	CR6		Home Health Care Certification						O			1							|	|
		2190	CR8		Pacemaker Certification								O			9							|	|
		2200	CRC		Conditions Indicator								O			100							|	|
	*	2310	HI		Health Care Information Codes						O			25							|	|
		2400	QTY		Quantity Information								O			10							|	|
		2410	HCP		Health Care Pricing									O			1							|	|
					\\--[ LOOP ID - 2305 ]----------------------------------------------------------[ 6 ]-------\	|	|
		2420	CR7		Home Health Treatment Plan Certification			O			1						|	|	|
		2430	HSD		Health Care Services Delivery						O			12						|	|	|
					//------------------------------------------------------------------------------------------/	|	|
					\\--[ LOOP ID - 2310 ]----------------------------------------------------------[ 9 ]-------\	|	|
	*	2500	NM1		Individual or Organizational Name					O			1						|	|	|
	*	2550	PRV		Provider Information								O			1						|	|	|
		2600	N2		Additional Name Information							O			2						|	|	|
	*	2650	N3		Party Location										O			2						|	|	|
	*	2700	N4		Geographic Location									O			1						|	|	|
		2710	REF		Reference Information								O			20						|	|	|
		2750	PER		Administrative Communications Contact				O			2						|	|	|
					//------------------------------------------------------------------------------------------/	|	|
					\\--[ LOOP ID - 2320 ]----------------------------------------------------------[ 10 ]------\	|	|
	*	2900	SBR		Subscriber Information								O			1						|	|	|
		2950	CAS		Claims Adjustment									O			99						|	|	|
		3000	AMT		Monetary Amount Information							O			15						|	|	|
		3050	DMG		Demographic Information								O			1						|	|	|
		3100	OI		Other Health Insurance Information					O			1						|	|	|
		3150	MIA		Medicare Inpatient Adjudication						O			1						|	|	|
		3200	MOA		Medicare Outpatient Adjudication					O			1						|	|	|
					\\--[ LOOP ID - 2330 ]----------------------------------------------------------[ 10 ]--\	|	|	|
	*	3250	NM1		Individual or Organizational Name					O			1					|	|	|	|
		3300	N2		Additional Name Information							O			2					|	|	|	|
	*	3320	N3		Party Location										O			2					|	|	|	|
	*	3400	N4		Geographic Location									O			1					|	|	|	|
		3450	PER		Administrative Communications Contact				O			2					|	|	|	|
	*	3500	DTP		Date or Time or Period								O			9					|	|	|	|
	*	3550	REF		Reference Information								O			>1					|	|	|	|
					//--------------------------------------------------------------------------------------/	|	|	|
					//------------------------------------------------------------------------------------------/	|	|
					\\--[ LOOP ID - 2400 ]----------------------------------------------------------[ >1 ]------\	|	|
	*	3650	LX		Transaction Set Line Number							O			1						|	|	|
	*	3700	SV1		Professional Service								O			1						|	|	|
		3750	SV2		Institutional Service								O			1						|	|	|
		3800	SV3		Dental Service										O			1						|	|	|
		3820	TOO		Tooth Identification								O			32						|	|	|
		3850	SV4		Drug Service										O			1						|	|	|
		4000	SV5		Durable Medical Equipment Service					O			1						|	|	|
		4050	SV6		Anesthesia Service									O			1						|	|	|
		4100	SV7		Drug Adjudication									O			1						|	|	|
	*	4150	HI		Health Care Information Codes						O			25						|	|	|
	*	4200	PWK		Paperwork											O			10						|	|	|
		4250	CR1		Ambulance Certification								O			1						|	|	|
		4300	CR2		Chiropractic Certification							O			5						|	|	|
		4350	CR3		Durable Medical Equipment Certification				O			1						|	|	|
		4400	CR4		Enteral or Parenteral Therapy Certification			O			3						|	|	|
		4450	CR5		Oxygen Therapy Certification						O			1						|	|	|
		4500	CRC		Conditions Indicator								O			3						|	|	|
	*	4550	DTP		Date or Time or Period								O			15						|	|	|
		4600	QTY		Quantity Information								O			5						|	|	|
		4620	MEA		Measurements										O			20						|	|	|
		4650	CN1		Contract Information								O			1						|	|	|
		4700	REF		Reference Information								O			30						|	|	|
		4750	AMT		Monetary Amount Information							O			15						|	|	|
	*	4800	K3		File Information									O			10						|	|	|
	*	4850	NTE		Note/Special Instruction							O			10						|	|	|
		4880	PS1		Purchase Service									O			1						|	|	|
		4900	IMM		Immunization Status									O			>1						|	|	|
		4910	HSD		Health Care Services Delivery						O			1						|	|	|
		4920	HCP		Health Care Pricing									O			1						|	|	|
					\\--[ LOOP ID - 2410 ]----------------------------------------------------------[ >1 ]--\	|	|	|
		4930	LIN		Item Identification									O			1					|	|	|	|
		4940	CTP		Pricing Information									O			1					|	|	|	|
		4950	REF		Reference Information								O			1					|	|	|	|
					//--------------------------------------------------------------------------------------/	|	|	|
					\\--[ LOOP ID - 2420 ]----------------------------------------------------------[ 10 ]--\	|	|	|
	*	5000	NM1		Individual or Organizational Name					O			1					|	|	|	|
	*	5050	PRV		Provider Information								O			1					|	|	|	|
		5100	N2		Additional Name Information							O			2					|	|	|	|
	*	5140	N3		Party Location										O			2					|	|	|	|
	*	5200	N4		Geographic Location									O			1					|	|	|	|
		5250	REF		Reference Information								O			20					|	|	|	|
		5300	PER		Administrative Communications Contact				O			2					|	|	|	|
					//--------------------------------------------------------------------------------------/	|	|	|
					\\--[ LOOP ID - 2430 ]----------------------------------------------------------[ >1 ]--\	|	|	|
		5400	SVD		Service Line Adjudication							O			1					|	|	|	|
		5450	CAS		Claims Adjustment									O			99					|	|	|	|
	*	5500	DTP		Date or Time or Period								O			9					|	|	|	|
		5505	AMT		Monetary Amount Information							O			20					|	|	|	|
					//--------------------------------------------------------------------------------------/	|	|	|
					\\--[ LOOP ID - 2440 ]----------------------------------------------------------[ >1 ]--\	|	|	|
		5510	LQ		Industry Code Identification						O			1					|	|	|	|
		5520	FRM		Supporting Documentation							M			99					|	|	|	|
					//--------------------------------------------------------------------------------------/	|	|	|
					//------------------------------------------------------------------------------------------/	|	|
					//----------------------------------------------------------------------------------------------/	|
					//--------------------------------------------------------------------------------------------------/
		5550	SE		Transaction Set Trailer								M			1


#### X12 Specifications with *#& Standard Segments

	\\--[ FILE - Communications Transport Protocol ]----------------------------/
	\\--[ ISA - Interchange Control Header ]--------------------------------/	|
	\\--[ GS - Functional Group Header ]--------------------------------/	|	|
	\\--[ ST - Transaction Set Header ]-----------------------------/	|	|	|
		[ Data - BHT ]												|	|	|	|
	\\--[ LOOP 1000 - 10 ]--------------------------------------/	|	|	|	|
		[ Data - NM1, N3, N4 ]									|	|	|	|	|
	//----------------------------------------------------------\	|	|	|	|
	\\--[ LOOP 2000 - >1 ]--------------------------------------/	|	|	|	|
		[ Data - PRV, SBR, PAT ]								|	|	|	|	|
	\\--[ LOOP 2010 - 10 ]----------------------------------/	|	|	|	|	|
		[ Data - NM1, N3, N4, DMG, REF ]					|	|	|	|	|	|
	//------------------------------------------------------\	|	|	|	|	|
	\\--[ LOOP 2300 - 100 ]---------------------------------/	|	|	|	|	|
		[ Data - CLM, DTP, REF, NTE, HI ]					|	|	|	|	|	|
	\\--[ LOOP 2305 - 6 ]-------------------------------\	|	|	|	|	|	|
		[ Data - *NONE* ]								|	|	|	|	|	|	|
	//--------------------------------------------------\	|	|	|	|	|	|
	\\--[ LOOP 2310 - 9 ]-------------------------------/	|	|	|	|	|	|
		[ Data - NM1, N3, N4, PRV ]						|	|	|	|	|	|	|
	//--------------------------------------------------\	|	|	|	|	|	|
	\\--[ LOOP 2320 - 10 ]------------------------------/	|	|	|	|	|	|
		[ Data - SBR ]									|	|	|	|	|	|	|
	\\--[ LOOP 2330 - 10 ]--------------------------/	|	|	|	|	|	|	|
		[ Data - NM1, N3, N4, REF ]					|	|	|	|	|	|	|	|
	//----------------------------------------------\	|	|	|	|	|	|	|
	//--------------------------------------------------\	|	|	|	|	|	|
	\\--[ LOOP 2400 - >1 ]------------------------------/	|	|	|	|	|	|
		[ Data - SV1, DTP, NTE ]						|	|	|	|	|	|	|
	\\--[ LOOP 2410 - >1 ]--------------------------/	|	|	|	|	|	|	|
		[ Data - *NONE* ]							|	|	|	|	|	|	|	|
	//----------------------------------------------\	|	|	|	|	|	|	|
	\\--[ LOOP 2420 - 10 ]--------------------------/	|	|	|	|	|	|	|
		[ Data - NM1, N3, N4, PRV ]					|	|	|	|	|	|	|	|
	//----------------------------------------------\	|	|	|	|	|	|	|
	\\--[ LOOP 2430 - >1 ]--------------------------/	|	|	|	|	|	|	|
		[ Data - *NONE* ]							|	|	|	|	|	|	|	|
	//----------------------------------------------\	|	|	|	|	|	|	|
	\\--[ LOOP 2440 - >1 ]--------------------------/	|	|	|	|	|	|	|
		[ Data - *NONE* ]							|	|	|	|	|	|	|	|
	//----------------------------------------------\	|	|	|	|	|	|	|
	//--------------------------------------------------\	|	|	|	|	|	|
	//------------------------------------------------------\	|	|	|	|	|
	//----------------------------------------------------------\	|	|	|	|
	//--[ SE - Transaction Set Trailer ]----------------------------\	|	|	|
	//--[ GE - Functional Group Trailer ]-------------------------------\	|	|
	\\--[ GS - Functional Group Header ]--------------------------------/	|	|
	\\--[ ST - Transaction Set Header ]-----------------------------/	|	|	|
		{...}														|	|	|	|
	//--[ SE - Transaction Set Trailer ]----------------------------\	|	|	|
	//--[ GE - Functional Group Trailer ]-------------------------------\	|	|
	//--[ IEA - Interchange Control Trailer ]-------------------------------\	|
	//--[ FILE - Communications Transport Trailer ]-----------------------------\


#### 837 Segment Detail

	ST		Transaction Set Header
	BHT		Beginning of Hierarchical Transaction
		\\ 1000A — SUBMITTER NAME
			NM1		Submitter Name
			PER		Submitter EDI Contact Information
		\\ 1000B — RECEIVER NAME
			NM1		Receiver Name
		\\ 2000A — BILLING PROVIDER HIERARCHICAL LEVEL
			HL		Billing Provider Hierarchical Level
			PRV		Billing Provider Specialty Information
			CUR		Foreign Currency Information
			\\ 2010AA — BILLING PROVIDER NAME
				NM1		Billing Provider Name
				N3		Billing Provider Address
				N4		Billing Provider City, State, ZIP Code
				REF		Billing Provider Tax Identification
				REF		Billing Provider UPIN/License Information
				PER		Billing Provider Contact Information
			\\ 2010AB — PAY-TO ADDRESS NAME
				NM1		Pay-to Address Name
				N3		Pay-to Address - ADDRESS
				N4		Pay-To Address City, State, ZIP Code
			\\ 2010AC — PAY-TO PLAN NAME
				NM1		Pay-To Plan Name
				N3		Pay-to Plan Address
				N4		Pay-To Plan City, State, ZIP Code
				REF		Pay-to Plan Secondary Identification
				REF		Pay-To Plan Tax Identification Number
		\\ 2000B — SUBSCRIBER HIERARCHICAL LEVEL
			HL		Subscriber Hierarchical Level
			SBR		Subscriber Information
			PAT		Patient Information
			\\ 2010BA — SUBSCRIBER NAME
				NM1		Subscriber Name
				N3		Subscriber Address
				N4		Subscriber City, State, ZIP Code
				DMG		Subscriber Demographic Information
				REF		Subscriber Secondary Identification
				REF		Property and Casualty Claim Number
				PER		Property and Casualty Subscriber Contact Information
			\\ 2010BB — PAYER NAME
				NM1		Payer Name
				N3		Payer Address
				N4		Payer City, State, ZIP Code
				REF		Payer Secondary Identification
				REF		Billing Provider Secondary Identification
		\\ 2000C — PATIENT HIERARCHICAL LEVEL
			HL		Patient Hierarchical Level
			PAT		Patient Information
			\\ 2010CA — PATIENT NAME
				NM1		Patient Name
				N3		Patient Address
				N4		Patient City, State, ZIP Code
				DMG		Patient Demographic Information
				REF		Property and Casualty Claim Number
				REF		Property and Casualty Patient Identifier
				PER		Property and Casualty Patient Contact Information
			\\ 2300 — CLAIM INFORMATION
				CLM		Claim Information
				DTP		Date - Onset of Current Illness or Symptom
				DTP		Date - Initial Treatment Date
				DTP		Date - Last Seen Date
				DTP		Date - Acute Manifestation
				DTP		Date - Accident
				DTP		Date - Last Menstrual Period
				DTP		Date - Last X-ray Date
				DTP		Date - Hearing and Vision Prescription Date
				DTP		Date - Disability Dates
				DTP		Date - Last Worked
				DTP		Date - Authorized Return to Work
				DTP		Date - Admission
				DTP		Date - Discharge
				DTP		Date - Assumed and Relinquished Care Dates
				DTP		Date - Property and Casualty Date of First Contact
				DTP		Date - Repricer Received Date
				PWK		Claim Supplemental Information
				CN1		Contract Information
				AMT		Patient Amount Paid
				REF		Service Authorization Exception Code
				REF		Mandatory Medicare (Section 4081) Crossover Indicator
				REF		Mammography Certification Number
				REF		Referral Number
				REF		Prior Authorization
				REF		Payer Claim Control Number
				REF		Clinical Laboratory Improvement Amendment (CLIA) Number
				REF		Repriced Claim Number
				REF		Adjusted Repriced Claim Number
				REF		Investigational Device Exemption Number
				REF		Claim Identifier For Transmission Intermediaries
				REF		Medical Record Number
				REF		Demonstration Project Identifier
				REF		Care Plan Oversight
				K3		File Information
				NTE		Claim Note
				CR1		Ambulance Transport Information
				CR2		Spinal Manipulation Service Information
				CRC		Ambulance Certification
				CRC		Patient Condition Information: Vision
				CRC		Homebound Indicator
				CRC		EPSDT Referral
				HI		Health Care Diagnosis Code
				HI		Anesthesia Related Procedure
				HI		Condition Information
				HCP		Claim Pricing/Repricing Information
				\\ 2310A — REFERRING PROVIDER NAME
					NM1		Referring Provider Name
					REF		Referring Provider Secondary Identification
				\\ 2310B — RENDERING PROVIDER NAME
					NM1		Rendering Provider Name
					PRV		Rendering Provider Specialty Information
					REF		Rendering Provider Secondary Identification
				\\ 2310C — SERVICE FACILITY LOCATION NAME
					NM1		Service Facility Location Name
					N3		Service Facility Location Address
					N4		Service Facility Location City, State, ZIP Code
					REF		Service Facility Location Secondary Identification
					PER		Service Facility Contact Information
				\\ 2310D — SUPERVISING PROVIDER NAME
					NM1		Supervising Provider Name
					REF		Supervising Provider Secondary Identification
				\\ 2310E — AMBULANCE PICK-UP LOCATION
					NM1		Ambulance Pick-up Location
					N3		Ambulance Pick-up Location Address
					N4		Ambulance Pick-up Location City, State, ZIP Code
				\\ 2310F — AMBULANCE DROP-OFF LOCATION
					NM1		Ambulance Drop-off Location
					N3		Ambulance Drop-off Location Address
					N4		Ambulance Drop-off Location City, State, ZIP Code
				\\ 2320 — OTHER SUBSCRIBER INFORMATION
					SBR		Other Subscriber Information
					CAS		Claim Level Adjustments
					AMT		Coordination of Benefits (COB) Payer Paid Amount
					AMT		Coordination of Benefits (COB) Total Non-Covered Amount
					AMT		Remaining Patient Liability
					OI		Other Insurance Coverage Information
					MOA		Outpatient Adjudication Information
					\\ 2330A — OTHER SUBSCRIBER NAME
						NM1		Other Subscriber Name
						N3		Other Subscriber Address
						N4		Other Subscriber City, State, ZIP Code
						REF		Other Subscriber Secondary Identification
					\\ 2330B — OTHER PAYER NAME
						NM1		Other Payer Name
						N3		Other Payer Address
						N4		Other Payer City, State, ZIP Code
						DTP		Claim Check or Remittance Date
						REF		Other Payer Secondary Identifier
						REF		Other Payer Prior Authorization Number
						REF		Other Payer Referral Number
						REF		Other Payer Claim Adjustment Indicator
						REF		Other Payer Claim Control Number
					\\ 2330C — OTHER PAYER REFERRING PROVIDER
						NM1		Other Payer Referring Provider
						REF		Other Payer Referring Provider Secondary Identification
					\\ 2330D — OTHER PAYER RENDERING PROVIDER
						NM1		Other Payer Rendering Provider
						REF		Other Payer Rendering Provider Secondary Identification
					\\ 2330E — OTHER PAYER SERVICE FACILITY LOCATION
						NM1		Other Payer Service Facility Location
						REF		Other Payer Service Facility Location Secondary Identification
					\\ 2330F — OTHER PAYER SUPERVISING PROVIDER
						NM1		Other Payer Supervising Provider
						REF		Other Payer Supervising Provider Secondary Identification
					\\ 2330G — OTHER PAYER BILLING PROVIDER
						NM1		Other Payer Billing Provider
						REF		Other Payer Billing Provider Secondary Identification
				\\ 2400 — SERVICE LINE NUMBER
					LX		Service Line Number
					SV1		Professional Service
					SV5		Durable Medical Equipment Service
					PWK		Line Supplemental Information
					PWK		Durable Medical Equipment Certificate of Medical Necessity Indicator
					CR1		Ambulance Transport Information
					CR3		Durable Medical Equipment Certification
					CRC		Ambulance Certification
					CRC		Hospice Employee Indicator
					CRC		Condition Indicator/Durable Medical Equipment
					DTP		Date - Service Date
					DTP		Date - Prescription Date
					DTP		DATE - Certification Revision/Recertification Date
					DTP		Date - Begin Therapy Date
					DTP		Date - Last Certification Date
					DTP		Date - Last Seen Date
					DTP		Date - Test Date
					DTP		Date - Shipped Date
					DTP		Date - Last X-ray Date
					DTP		Date - Initial Treatment Date
					QTY		Ambulance Patient Count
					QTY		Obstetric Anesthesia Additional Units
					MEA		Test Result
					CN1		Contract Information
					REF		Repriced Line Item Reference Number
					REF		Adjusted Repriced Line Item Reference Number
					REF		Prior Authorization
					REF		Line Item Control Number
					REF		Mammography Certification Number
					REF		Clinical Laboratory Improvement Amendment (CLIA) Number
					REF		Referring Clinical Laboratory Improvement Amendment (CLIA) Facility Identification
					REF		Immunization Batch Number
					REF		Referral Number
					AMT		Sales Tax Amount
					AMT		Postage Claimed Amount
					K3		File Information
					NTE		Line Note
					NTE		Third Party Organization Notes
					PS1		Purchased Service Information
					HCP		Line Pricing/Repricing Information
					\\ 2410 — DRUG IDENTIFICATION
						LIN		Drug Identification
						CTP		Drug Quantity
						REF		Prescription or Compound Drug Association Number
					\\ 2420A — RENDERING PROVIDER NAME
						NM1		Rendering Provider Name
						PRV		Rendering Provider Specialty Information
						REF		Rendering Provider Secondary Identification
					\\ 2420B — PURCHASED SERVICE PROVIDER NAME
						NM1		Purchased Service Provider Name
						REF		Purchased Service Provider Secondary Identification
					\\ 2420C — SERVICE FACILITY LOCATION NAME
						NM1		Service Facility Location Name
						N3		Service Facility Location Address
						N4		Service Facility Location City, State, ZIP Code
						REF		Service Facility Location Secondary Identification
					\\ 2420D — SUPERVISING PROVIDER NAME
						NM1		Supervising Provider Name
						REF		Supervising Provider Secondary Identification
					\\ 2420E — ORDERING PROVIDER NAME
						NM1		Ordering Provider Name
						N3		Ordering Provider Address
						N4		Ordering Provider City, State, ZIP Code
						REF		Ordering Provider Secondary Identification
						PER		Ordering Provider Contact Information
					\\ 2420F — REFERRING PROVIDER NAME
						NM1		Referring Provider Name
						REF		Referring Provider Secondary Identification
					\\ 2420G — AMBULANCE PICK-UP LOCATION
						NM1		Ambulance Pick-up Location
						N3		Ambulance Pick-up Location Address
						N4		Ambulance Pick-up Location City, State, ZIP Code
					\\ 2420H — AMBULANCE DROP-OFF LOCATION
						NM1		Ambulance Drop-off Location
						N3		Ambulance Drop-off Location Address
						N4		Ambulance Drop-off Location City, State, ZIP Code
					\\ 2430 — LINE ADJUDICATION INFORMATION
						SVD		Line Adjudication Information
						CAS		Line Adjustment
						DTP		Line Check or Remittance Date
						AMT		Remaining Patient Liability
					\\ 2440 — FORM IDENTIFICATION CODE
						LQ		Form Identification Code
						FRM		Supporting Documentation
	SE		Transaction Set Trailer


#### Segment Conditions

	BHT	(BHT06		= [ RP ])
	CLM	(ALL)
	DMG	(ALL)
	DTP	(DTP01		= [ 431 | 472 ])
	HI	(HI01-1		= [ ABK | BK ])
	N3	(NM101		= [ IL | PR | QC | DK | 77 | 85 | 41 | 87 ])
	N4	(NM101		= [ IL | PR | QC | DK | 77 | 85 | 41 | 87 ])
	NM1	(NM101		= [ IL | PR | QC | DN | DK | DQ | 82 | 77 | 85 | 41 | 87 ])
	NTE	(NTE01		= [ ADD ])
	PAT	(SBR01		= [ P | S | T ] & SBR02 = [ 18 ])
	PRV	(PRV01		= [ BI | PE ] & PRV02 = [ PXC ])
	REF	(REF01		= [ EI | EA ])
	SBR	(SBR01		= [ P | S | T ])
	SV1	(SV101-1	= [ HC | WK ])


#### Segment Detail Conditions
	\\ Transaction Set Header
		BHT (x1)
			- BHT06 = [ RP ]
		? REF (x3)
			- REF01 = [ EI | EA ]
		\\ 1000A — SUBMITTER NAME
			NM1		Submitter Name (x1)
				- NM101 = [ 41 ]
			? N3
				- NM101 = [ 41 ]
			? N4
				- NM101 = [ 41 ]
		\\ 1000B — RECEIVER NAME
			NM1		Receiver Name (x1)
				- NM101 = [ 40 ] & NM102 = [ 2 ]
		\\ 2000A — BILLING PROVIDER HIERARCHICAL LEVEL
			PRV		Billing Provider Specialty Information (x1)
				- PRV01 = [ BI ]
				- PRV02 = [ PXC ]
			\\ 2010AA — BILLING PROVIDER NAME
				NM1		Billing Provider Name (x1)
					- NM101 = [ 85 ]
				N3		Billing Provider Address (x2)
					- NM101 = [ 85 ]
				N4		Billing Provider City, State, ZIP Code (x1)
					- NM101 = [ 85 ]
				REF		Billing Provider Tax Identification (x2)
					- REF01 = [ EI ]
			\\ 2010AB — PAY-TO ADDRESS NAME
				NM1		Pay-to Address Name (x1)
					- NM101 = [ 87 ]
				N3		Pay-to Address - ADDRESS (x2)
					- NM101 = [ 87 ]
				N4		Pay-To Address City, State, ZIP Code (x1)
					- NM101 = [ 87 ]
			\\ 2010AC — PAY-TO PLAN NAME
				? NM1	Pay-To Plan Name (x1)
					- NM101 = [ PE ]
				? N3	Pay-to Plan Address (x2)
					- NM101 = [ PE ]
				? N4	Pay-To Plan City, State, ZIP Code (x1)
					- NM101 = [ PE ]
				? REF	Pay-To Plan Tax Identification Number (x20)
					- REF01 = [ EI ]
		\\ 2000B — SUBSCRIBER HIERARCHICAL LEVEL
			SBR		Subscriber Information (x1)
				- SBR01 = [ P | S | T ]
			PAT		Patient Information (x1)
				- SBR01 = [ P | S | T ] & SBR02 = [ 18 ]
			\\ 2010BA — SUBSCRIBER NAME
				NM1		Subscriber Name (x1)
					- NM101 = [ IL ]
				N3		Subscriber Address (x2)
					- NM101 = [ IL ]
				N4		Subscriber City, State, ZIP Code (x1)
					- NM101 = [ IL ]
				DMG		Subscriber Demographic Information (x1)
					- ALL
			\\ 2010BB — PAYER NAME
				NM1		Payer Name (x1)
					- NM101 = [ PR ]
				N3		Payer Address (x2)
					- NM101 = [ PR ]
				N4		Payer City, State, ZIP Code (x1)
					- NM101 = [ PR ]
				REF		Payer Secondary Identification (x20)
					- REF01 = [ EI ]
		\\ 2000C — PATIENT HIERARCHICAL LEVEL
			? PAT		Patient Information (x1)
				- SBR01 = [ P | S | T ] & SBR02 = [ 18 ]
			\\ 2010CA — PATIENT NAME
				NM1		Patient Name (x1)
					- NM101 = [ QC ]
				N3		Patient Address (x2)
					- NM101 = [ QC ]
				N4		Patient City, State, ZIP Code (x1)
					- NM101 = [ QC ]
				DMG		Patient Demographic Information (x1)
					- ALL
			\\ 2300 — CLAIM INFORMATION
				CLM		Claim Information (x1)
					- ALL
				DTP		Date - Onset of Current Illness or Symptom (x150)
					- DTP01 = [ 431 ]
				REF		Medical Record Number (x30)
					- REF01 = [ EA ]
				NTE		Claim Note (x20)
					- NTE01 = [ ADD ]
				HI		Health Care Diagnosis Code (x25)
					- HI01-1 = [ ABK | BK ]
				\\ 2310A — REFERRING PROVIDER NAME
					NM1		Referring Provider Name (x1)
						- NM101 = [ DN ]
				\\ 2310B — RENDERING PROVIDER NAME
					NM1		Rendering Provider Name (x1)
						- NM101 = [ 82 ]
					PRV		Rendering Provider Specialty Information (x1)
						- PRV01 = [ PE ] & PRV02 = [ PXC ]
				\\ 2310C — SERVICE FACILITY LOCATION NAME
					NM1		Service Facility Location Name (x1)
						- NM101 = [ 77 ]
					N3		Service Facility Location Address (x2)
						- NM101 = [ 77 ]
					N4		Service Facility Location City, State, ZIP Code (x1)
						- NM101 = [ 77 ]
				\\ 2310D — SUPERVISING PROVIDER NAME
					NM1		Supervising Provider Name (x1)
						- NM101 = [ DQ ]
				\\ 2310E — AMBULANCE PICK-UP LOCATION
				\\ 2310F — AMBULANCE DROP-OFF LOCATION
				\\ 2320 — OTHER SUBSCRIBER INFORMATION
					SBR		Other Subscriber Information (x1)
						- SBR01 = [ P | S | T ]
					\\ 2330A — OTHER SUBSCRIBER NAME
						NM1		Other Subscriber Name (x1)
							- NM101 = [ IL ]
						N3		Other Subscriber Address (x2)
							- NM101 = [ IL ]
						N4		Other Subscriber City, State, ZIP Code (x1)
							- NM101 = [ IL ]
					\\ 2330B — OTHER PAYER NAME
						NM1		Other Payer Name (x1)
							- NM101 = [ PR ]
						N3		Other Payer Address (x2)
							- NM101 = [ PR ]
						N4		Other Payer City, State, ZIP Code (x1)
							- NM101 = [ PR ]
						REF		Other Payer Secondary Identifier (>1)
							- REF01 = [ EI ]
					\\ 2330C — OTHER PAYER REFERRING PROVIDER
						NM1		Other Payer Referring Provider (x1)
							- NM101 = [ DN ]
					\\ 2330D — OTHER PAYER RENDERING PROVIDER
						NM1		Other Payer Rendering Provider (x1)
							- NM101 = [ 82 ]
					\\ 2330E — OTHER PAYER SERVICE FACILITY LOCATION
						NM1		Other Payer Service Facility Location (x1)
							- NM101 = [ 77 ]
					\\ 2330F — OTHER PAYER SUPERVISING PROVIDER
						NM1		Other Payer Supervising Provider (x1)
							- NM101 = [ DQ ]
					\\ 2330G — OTHER PAYER BILLING PROVIDER
						NM1		Other Payer Billing Provider (x1)
							- NM101 = [ 85 ]
				\\ 2400 — SERVICE LINE NUMBER
					SV1		Professional Service (x1)
						- SV101-1 = [ HC | WK ]
					DTP		Date - Service Date (x15)
						- DTP01 = [ 472 ]
					NTE		Line Note (x10)
						- NTE01 = [ ADD ]
					\\ 2410 — DRUG IDENTIFICATION
					\\ 2420A — RENDERING PROVIDER NAME
						NM1		Rendering Provider Name (x1)
							- NM101 = [ 82 ]
						PRV		Rendering Provider Specialty Information (x1)
							- PRV01 = [ PE ] & PRV02 = [ PXC ]
					\\ 2420B — PURCHASED SERVICE PROVIDER NAME
					\\ 2420C — SERVICE FACILITY LOCATION NAME
						NM1		Service Facility Location Name (x1)
							- NM101 = [ 77 ]
						N3		Service Facility Location Address (x2)
							- NM101 = [ 77 ]
						N4		Service Facility Location City, State, ZIP Code (x1)
							- NM101 = [ 77 ]
					\\ 2420D — SUPERVISING PROVIDER NAME
						NM1		Supervising Provider Name (x1)
							- NM101 = [ DQ ]
					\\ 2420E — ORDERING PROVIDER NAME
						NM1		Ordering Provider Name (x1)
							- NM101 = [ DK ]
						N3		Ordering Provider Address (x2)
							- NM101 = [ DK ]
						N4		Ordering Provider City, State, ZIP Code (x1)
							- NM101 = [ DK ]
					\\ 2420F — REFERRING PROVIDER NAME
						NM1		Referring Provider Name (x1)
 							- NM101 = [ DN ]
					\\ 2420G — AMBULANCE PICK-UP LOCATION
					\\ 2420H — AMBULANCE DROP-OFF LOCATION
					\\ 2430 — LINE ADJUDICATION INFORMATION
					\\ 2440 — FORM IDENTIFICATION CODE



