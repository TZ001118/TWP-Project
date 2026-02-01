<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frequently Asked Questions | Furniture Direct</title>
    
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body {
            background-color: #f9f9f9;
        }

        .faq-header {
            text-align: center;
            padding: 60px 20px 40px;
            margin-top: 200px;
        }

        .faq-header h1 {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .faq-header p {
            color: #666;
            font-size: 16px;
        }

        .faq-container {
            max-width: 800px;
            margin: 0 auto 80px;
            padding: 0 20px;
        }

        .faq-item {
            background-color: #fff;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            overflow: hidden;
            border: 1px solid #eee;
            transition: all 0.3s ease;
        }

        .faq-question {
            width: 100%;
            background: none;
            border: none;
            padding: 20px 25px;
            text-align: left;
            font-size: 16px;
            font-weight: 600;
            color: #333;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s;
        }

        .faq-question:hover {
            background-color: #f8f8f8;
        }

        .faq-item.active {
            border-color: #99d5c5;
            box-shadow: 0 4px 10px rgba(153, 213, 197, 0.2);
        }

        .faq-item.active .faq-question {
            color: #2c7a68;
            background-color: #eef9f6;
        }

        .faq-icon {
            font-size: 18px;
            color: #999;
            transition: transform 0.3s ease;
        }

        .faq-item.active .faq-icon {
            transform: rotate(45deg);
            color: #2c7a68;
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            background-color: #fff;
        }

        .faq-answer-content {
            padding: 0 25px 25px;
            color: #555;
            line-height: 1.6;
            font-size: 14px;
        }

        .faq-answer-content ul {
            padding-left: 20px;
            margin: 10px 0;
        }

        .faq-answer-content li {
            margin-bottom: 5px;
        }

        .contact-link {
            color: #99d5c5;
            font-weight: bold;
            text-decoration: none;
        }
        .contact-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="faq-header">
        <h1>Frequently Asked Questions</h1>
        <p>Here are some common questions about Furniture Direct.</p>
    </div>

    <div class="faq-container">

        <div class="faq-item">
            <button class="faq-question">
                How do I place an order?
                <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Ordering is easy! Just follow these steps:</p>
                    <ol>
                        <li>Browse our products and click <strong>“Add to Cart”</strong> on the items you like.</li>
                        <li>Once done, go to your Cart and click <strong>“Checkout”</strong>.</li>
                        <li>Log in to your account (or register if you are new).</li>
                        <li>Confirm your <strong>Shipping Address</strong>.</li>
                        <li>Select your preferred <strong>Payment Method</strong>.</li>
                        <li>Click <strong>“Confirm Order”</strong> to complete your purchase.</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                What payment methods do you accept?
                <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>We currently accept the following payment methods:</p>
                    <ul>
                        <li><strong>Credit/Debit Card:</strong> Secure Touch 'n Go, Grap pay and Bank Transfer</li>
                        <li><strong>Bank Transfer (Offline):</strong> You can transfer manually to our CIMB account.
                            <br><br>
                            <em>Bank Name:</em> CIMB<br>
                            <em>Account No:</em> 76-5272239-2<br>
                            <em>Account Name:</em> Domea Sdn. Bhd<br>
                            <br>
                            After transfer, please email your receipt and Order ID to <a href="mailto:Domea@gmail.com.my" class="contact-link">Domea@gmail.com.my</a> so we can process your order.
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                How do I change my shipping address or password?
                <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>You can manage your personal details easily:</p>
                    <p>Log in to your account, click on <strong>“your username”</strong> in the navigation bar. From your Dashboard, you can edit your Profile details, change your password, and manage your saved addresses.</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                I forgot my password. What should I do?
                <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Don't worry! Go to the Login page and click on the <strong>“Lost your password?”</strong> link.</p>
                    <p>Enter your registered email address or username, and we will allow you to reset your password immediately.</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                Can I cancel my order?
                <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Yes, but only if your order status is still <strong>“Pending”</strong>.</p>
                    <p>Once the order status changes to “Shipped” or “Processing”, we cannot cancel it. To request a cancellation, please email us immediately at <a href="mailto:domea@gmail.com.my" class="contact-link">domea@gmail.com.my</a> with the subject "Request to cancel order".</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                How long does delivery take?
                <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Standard delivery usually takes about <strong>7 - 15 working days</strong> depending on your location and stock availability.</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                I still have doubts!
                <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Sorry that we're unable to clear your doubts here. Please kindly send us an email to <a href="mailto:Domea@gmail.com.my" class="contact-link">Domea@gmail.com.my</a> and we will get back to you as soon as possible!</p>
                </div>
            </div>
        </div>

    </div>

    <script>
        const faqItems = document.querySelectorAll('.faq-item');

        faqItems.forEach(item => {
            const questionBtn = item.querySelector('.faq-question');
            const answerDiv = item.querySelector('.faq-answer');
            const icon = item.querySelector('.faq-icon i');

            questionBtn.addEventListener('click', () => {
                const isOpen = item.classList.contains('active');

                faqItems.forEach(otherItem => {
                    otherItem.classList.remove('active');
                    otherItem.querySelector('.faq-answer').style.maxHeight = null;
                    otherItem.querySelector('.faq-icon i').classList.remove('fa-minus');
                    otherItem.querySelector('.faq-icon i').classList.add('fa-plus');
                });

                if (!isOpen) {
                    item.classList.add('active');
                    answerDiv.style.maxHeight = answerDiv.scrollHeight + "px";
                    icon.classList.remove('fa-plus');
                    icon.classList.add('fa-minus');
                }
            });
        });

        window.onscroll = function() {
            const nav = document.querySelector('.navbar');
            if (nav) {
                if (window.scrollY > 20) {
                    nav.classList.add('collapsed');
                } else {
                    nav.classList.remove('collapsed');
                }
            }
        };
    </script>
</body>
</html>