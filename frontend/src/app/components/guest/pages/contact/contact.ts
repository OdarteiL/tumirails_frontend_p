import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { LucideAngularModule, MapPin, Phone, Mail, ChevronDown, ChevronUp } from 'lucide-angular';

@Component({
  selector: 'app-contact',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, LucideAngularModule],
  templateUrl: './contact.html',
  styleUrl: './contact.css'
})
export class ContactComponent {
  readonly MapPin = MapPin;
  readonly Phone = Phone;
  readonly Mail = Mail;
  readonly ChevronDown = ChevronDown;
  readonly ChevronUp = ChevronUp;

  contactForm: FormGroup;
  openFaqIndex: number | null = null;

  faqs = [
    {
      question: 'What is Tumirails and how does it benefit me?',
      answer: 'Tumirails is a decentralized energy marketplace that enables homeowners and businesses to trade excess solar power. It benefits you by lowering electricity bills, providing a reliable source of clean energy, and allowing you to earn money from your generated power.'
    },
    {
      question: 'How does peer-to-peer energy trading work on Tumirails?',
      answer: "Through our secure platform, energy 'blocks' are traded between producers (prosumers) and consumers. Smart meters track production and consumption in real-time, and our automated system matches buyers with sellers for seamless transactions."
    },
    {
      question: 'Is solar energy reliable in Ghana, especially during varying weather conditions?',
      answer: 'Yes, solar is highly reliable in Ghana due to abundant sunlight. Modern solar systems also include battery storage solutions to ensure you have power during the night or on cloudy days.'
    },
    {
      question: 'How do I become a prosumer on Tumirails and sell my excess solar energy?',
      answer: 'Simply sign up as a prosumer, register your solar installation details, and connect your smart meter. Our verification team will approve your account, and you can start listing your excess energy for sale immediately.'
    },
    {
      question: 'What are the benefits for utility companies partnering with Tumirails?',
      answer: 'Utility companies can reduce grid load during peak hours, lower infrastructure maintenance costs, and access valuable data on distributed energy resources, fostering a more resilient national grid.'
    },
    {
      question: 'How can I track my energy consumption and generation on the Tumirails platform?',
      answer: 'Our intuitive dashboard provides real-time analytics. You can view your daily usage, generation stats, savings, and carbon footprint reduction all in one place.'
    }
  ];

  constructor(private fb: FormBuilder) {
    this.contactForm = this.fb.group({
      name: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      subject: ['', Validators.required],
      message: ['', Validators.required]
    });
  }

  toggleFaq(index: number) {
    this.openFaqIndex = this.openFaqIndex === index ? null : index;
  }

  onSubmit() {
    if (this.contactForm.valid) {
      console.log('Message Sent:', this.contactForm.value);
      alert('Thank you for contacting us! We will get back to you shortly.');
      this.contactForm.reset();
    } else {
      this.contactForm.markAllAsTouched();
    }
  }
}
